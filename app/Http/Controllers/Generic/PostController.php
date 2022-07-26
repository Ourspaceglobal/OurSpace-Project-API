<?php

namespace App\Http\Controllers\Generic;

use App\Events\User\ViewLogger;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

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
        $posts = Post::published()
            ->select([
                'id',
                'admin_id',
                'title',
                'slug',
                'highlight',
                'is_published',
                'rating',
                'created_at',
                'updated_at',
            ])
            ->with([
                'admin:id,first_name,last_name',
            ])
            ->withCount([
                'comments' => fn($query) => $query->approved()->whereNull('parent_id'),
            ])
            ->when($request->search, function ($query, $search) {
                $query->fullText($search);
            })
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
     * Display the specified resource.
     *
     * @param Request $request
     * @param mixed $post
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $post)
    {
        $post = Post::query()
            ->with([
                'admin:id,first_name,last_name',
                'comments' => fn($query) => $query->approved()
                    ->latest()
                    ->whereNull('parent_id')
                    ->withCount('replies')
                    ->with([
                        'user:id,first_name,last_name',
                    ]),
                'reviews' => fn($query) => $query->approved()->latest()->with([
                    'user:id,first_name,last_name',
                ]),
            ])
            ->withCount([
                'views',
            ])
            ->where('id', $post)
            ->orWhere('slug', $post)
            ->firstOrFail();

        if (!$post->is_published) {
            if ($secret = $request->secret) {
                throw_if(
                    $post->secret_key !== $secret,
                    ModelNotFoundException::class,
                    'Post [secret] is invalid.'
                );

                $post->secret_key_last_used = now();
                $post->save();
            } else {
                throw new ModelNotFoundException('Post is still in drafts.');
            }
        } else {
            event(new ViewLogger($post, $request->user()));
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Post fetched successfully.')
            ->withData([
                'post' => $post,
            ])
            ->build();
    }
}
