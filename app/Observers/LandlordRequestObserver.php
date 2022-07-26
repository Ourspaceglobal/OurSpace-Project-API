<?php

namespace App\Observers;

use App\Enums\LandlordRequestStatuses;
use App\Events\User\ChangeTenantToLandlord;
use App\Models\Admin;
use App\Models\LandlordRequest;
use App\Notifications\Admin\LandlordRequestClosedNotification as AdminLandlordRequestClosedNotification;
use App\Notifications\Admin\LandlordRequestSubmittedNotification as AdminLandlordRequestSubmittedNotification;
use App\Notifications\User\LandlordRequestApprovedNotification;
use App\Notifications\User\LandlordRequestClosedNotification;
use App\Notifications\User\LandlordRequestDeclinedNotification;
use App\Notifications\User\LandlordRequestSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class LandlordRequestObserver
{
    /**
     * Handle the LandlordRequest "creating" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function creating(LandlordRequest $landlordRequest)
    {
        //
    }

    /**
     * Handle the LandlordRequest "created" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function created(LandlordRequest $landlordRequest)
    {
        $admins = Admin::permission('receive email notifications')->get();
        Notification::send($admins, new AdminLandlordRequestSubmittedNotification($landlordRequest));

        $landlordRequest->user->notify(new LandlordRequestSubmittedNotification());
    }

    /**
     * Handle the LandlordRequest "updating" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function updating(LandlordRequest $landlordRequest)
    {
        //
    }

    /**
     * Handle the LandlordRequest "updated" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function updated(LandlordRequest $landlordRequest)
    {
        if ($landlordRequest->status === LandlordRequestStatuses::CLOSED) {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminLandlordRequestClosedNotification($landlordRequest));

            $landlordRequest->user->notify(new LandlordRequestClosedNotification());
        }

        if ($landlordRequest->status === LandlordRequestStatuses::DECLINED) {
            $landlordRequest->user->notify(new LandlordRequestDeclinedNotification($landlordRequest));
        }

        if ($landlordRequest->status === LandlordRequestStatuses::APPROVED) {
            $landlordRequest->user->notify(new LandlordRequestApprovedNotification($landlordRequest));
            event(new ChangeTenantToLandlord($landlordRequest->user));
        }
    }

    /**
     * Handle the LandlordRequest "deleting" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function deleting(LandlordRequest $landlordRequest)
    {
        //
    }

    /**
     * Handle the LandlordRequest "deleted" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function deleted(LandlordRequest $landlordRequest)
    {
        //
    }

    /**
     * Handle the LandlordRequest "restored" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function restored(LandlordRequest $landlordRequest)
    {
        //
    }

    /**
     * Handle the LandlordRequest "force deleted" event.
     *
     * @param \App\Models\LandlordRequest $landlordRequest
     * @return void
     */
    public function forceDeleted(LandlordRequest $landlordRequest)
    {
        //
    }
}
