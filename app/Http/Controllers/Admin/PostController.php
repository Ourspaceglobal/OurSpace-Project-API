<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\BasicUserInfoExtract;
use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $posts = QueryBuilder::for(Post::class)
            ->allowedFilters([
                'title',
                AllowedFilter::trashed(),
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'title',
                'is_published',
                'updated_at',
                'created_at',
            ])
            ->allowedIncludes([
                AllowedInclude::custom('admin', new BasicUserInfoExtract()),
                AllowedInclude::count('commentsCount'),
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Posts fetched successfully.')
            ->withData([
                'posts' => $posts,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePostRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StorePostRequest $request)
    {
        DB::beginTransaction();

        $post = new Post();
        $post->admin_id = $request->user()->id;
        $post->title = $request->title;
        $post->highlight = $request->highlight;
        $post->body = $request->body;
        $post->save();

        $post->addMediaFromRequest('featured_image')->toMediaCollection(MediaCollection::FEATUREDIMAGE);

        // Tags
        $requestTags = array_map('trim', $request->tags);
        $tags = collect($requestTags)->map(fn ($item) => [
            'name' => $item,
            'id' => Str::orderedUuid()->toString(),
        ])->toArray();
        Tag::query()->upsert($tags, ['name'], ['name']);
        $post->tags()->sync(Tag::query()->whereIn('name', $requestTags)->pluck('id'));

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Post created successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($post)
    {
        $post = QueryBuilder::for(Post::withTrashed()->where('id', $post)->orWhere('slug', $post))
            ->allowedIncludes([
                AllowedInclude::custom('admin', new BasicUserInfoExtract()),
                'tags',
            ])
            ->with([
                'comments' => fn($query) => $query
                    ->latest()
                    ->whereNull('parent_id')
                    ->withCount('replies')
                    ->with([
                        'user' => fn($query) => $query->select(['id', 'first_name', 'last_name'])
                    ]),
            ])
            ->firstOrFail()
            ->makeVisible([
                'secret_key',
                'secret_key_last_used',
            ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Post fetched successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePostRequest $request
     * @param \App\Models\Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        DB::beginTransaction();

        $post->title = $request->title;
        $post->highlight = $request->highlight;
        $post->body = $request->body;
        $post->save();

        if ($request->featured_image) {
            $post->addMediaFromRequest('featured_image')->toMediaCollection(MediaCollection::FEATUREDIMAGE);
        }

        // Tags
        $requestTags = array_map('trim', $request->tags);
        $tags = collect($requestTags)->map(fn ($item) => [
            'name' => $item,
            'id' => Str::orderedUuid()->toString(),
        ])->toArray();
        Tag::query()->upsert($tags, ['name'], ['name']);
        $post->tags()->sync(Tag::query()->whereIn('name', $requestTags)->pluck('id'));

        DB::commit();

        $post->refresh();

        return ResponseBuilder::asSuccess()
            ->withMessage('Post updated successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }

    /**
     * Toggle published status of the specified resource in storage.
     *
     * @param \App\Models\Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function togglePublicationStatus(Post $post)
    {
        $post->is_published = !$post->is_published;
        $post->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Post updated successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(Post $post)
    {
        $post->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Post restored successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }
}
