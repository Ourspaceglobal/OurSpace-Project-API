<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreCommentRequest;
use App\Http\Requests\User\UpdateCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreCommentRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreCommentRequest $request)
    {
        $comment = new Comment();
        $comment->user()->associate($request->user());
        $comment->model_type = $request->model_type;
        $comment->model_id = $request->model_id;
        $comment->comment = $request->comment;
        $comment->parent_id = $request->parent_id;
        $comment->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Comment stored successfully.')
            ->withData([
                'comment' => $comment->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Comment $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCommentRequest $request
     * @param \App\Models\Comment $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $comment->comment = $request->comment;
        $comment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Comment updated successfully.')
            ->withData([
                'comment' => $comment,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Comment $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
