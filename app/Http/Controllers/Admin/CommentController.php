<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class CommentController extends Controller
{
    /**
     * Display specified resource from storage.
     *
     * @param Comment $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Comment $comment)
    {
        $comments = $comment->replies()
            ->latest()
            ->withCount('replies')
            ->with([
                'user' => fn($query) => $query->select(['id', 'first_name', 'last_name'])
            ])
            ->get();

        return ResponseBuilder::asSuccess()
            ->withMessage('Comment replies fetched successfully')
            ->withData(['comments' => $comments])
            ->build();
    }

    /**
     * Toggle approval status of the specified resource in storage.
     *
     * @param \App\Models\Comment $comment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleApprovalStatus(Comment $comment)
    {
        $comment->is_approved = !$comment->is_approved;
        $comment->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Comment approval status updated successfully.')
            ->withData([
                'comment' => $comment
            ])
            ->build();
    }
}
