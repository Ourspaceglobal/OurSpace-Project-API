<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Notifications fetched successfully.')
            ->withData([
                'notifications' => $notifications,
            ])
            ->build();
    }

    /**
     * Mark a notification as read.
     *
     * @param Request $request
     * @param mixed $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function read(Request $request, $notification)
    {
        $request->user()->notifications()->where('id', $notification)->firstOrFail()->markAsRead();

        return ResponseBuilder::asSuccess()
            ->withMessage('Notification marked as read successfully.')
            ->build();
    }

    /**
     * Mark all notifications as read.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return ResponseBuilder::asSuccess()
            ->withMessage('All notifications marked as read successfully.')
            ->build();
    }
}
