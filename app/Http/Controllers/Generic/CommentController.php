<?php

namespace App\Http\Controllers\Generic;

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
            ->approved()
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
}
