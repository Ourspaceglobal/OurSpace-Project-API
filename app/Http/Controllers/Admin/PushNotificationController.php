<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePushNotificationRequest;
use App\Http\Requests\Admin\UpdatePushNotificationRequest;
use App\Models\PushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PushNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $pushNotifications = QueryBuilder::for(PushNotification::class)
            ->allowedIncludes([
                'admin',
            ])
            ->defaultSort('-send_at')
            ->allowedSorts([
                'send_at',
                'updated_at',
                'created_at',
            ])
            ->allowedFilters([
                AllowedFilter::trashed(),
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Push notifications fetched successfully.')
            ->withData([
                'push_notifications' => $pushNotifications,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePushNotificationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StorePushNotificationRequest $request)
    {
        $pushNotification = new PushNotification();
        $pushNotification->admin()->associate($request->user());
        $pushNotification->subject = $request->subject;
        $pushNotification->message = $request->message;
        $pushNotification->send_at = $request->send_at;
        $pushNotification->send_via_mail = $request->send_via_mail;
        $pushNotification->send_via_system = $request->send_via_system;
        $pushNotification->user_ids = $request->user_ids;
        $pushNotification->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Push notification stored successfully.')
            ->withData([
                'push_notification' => $pushNotification->unsetRelation('admin'),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param mixed $pushNotification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show($pushNotification)
    {
        $pushNotification = QueryBuilder::for(PushNotification::query()->where('id', $pushNotification))
            ->allowedIncludes([
                'admin',
            ])
            ->firstOrFail();

        $pushNotification->users = $pushNotification->users();

        return ResponseBuilder::asSuccess()
            ->withMessage('Push notification fetched successfully.')
            ->withData([
                'push_notification' => $pushNotification,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePushNotificationRequest $request
     * @param \App\Models\PushNotification $pushNotification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdatePushNotificationRequest $request, PushNotification $pushNotification)
    {
        $pushNotification->subject = $request->subject;
        $pushNotification->message = $request->message;
        $pushNotification->send_at = $request->send_at;
        $pushNotification->send_via_mail = $request->send_via_mail;
        $pushNotification->send_via_system = $request->send_via_system;
        $pushNotification->user_ids = $request->user_ids;
        $pushNotification->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Push notification updated successfully.')
            ->withData([
                'push_notification' => $pushNotification,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\PushNotification $pushNotification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(PushNotification $pushNotification)
    {
        $pushNotification->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param \App\Models\PushNotification $pushNotification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function restore(PushNotification $pushNotification)
    {
        abort_if($pushNotification->send_at < now(), 403, 'Sending date is now in the past');

        $pushNotification->restore();

        return ResponseBuilder::asSuccess()
            ->withMessage('Push notification restored successfully.')
            ->withData([
                'push_notification' => $pushNotification,
            ])
            ->build();
    }
}
