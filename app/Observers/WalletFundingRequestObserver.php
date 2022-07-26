<?php

namespace App\Observers;

use App\Enums\WalletFundingStatuses;
use App\Events\User\WalletHistoryRecorder;
use App\Models\Admin;
use App\Models\WalletFundingRequest;
use App\Notifications\Admin\WalletFundingRequestClosedNotification as AdminWalletFundingRequestClosedNotification;
use App\Notifications\Admin\WalletFundingRequestSubmittedNotification as AdminWalletFundingRequestSubmittedNotification;
use App\Notifications\User\WalletFundingRequestApprovedNotification;
use App\Notifications\User\WalletFundingRequestClosedNotification;
use App\Notifications\User\WalletFundingRequestDeclinedNotification;
use App\Notifications\User\WalletFundingRequestSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class WalletFundingRequestObserver
{
    /**
     * Handle the WalletFundingRequest "creating" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function creating(WalletFundingRequest $walletFundingRequest)
    {
        //
    }

    /**
     * Handle the WalletFundingRequest "created" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function created(WalletFundingRequest $walletFundingRequest)
    {
        $admins = Admin::permission('receive email notifications')->get();
        Notification::send($admins, new AdminWalletFundingRequestSubmittedNotification($walletFundingRequest));

        $walletFundingRequest->user->notify(new WalletFundingRequestSubmittedNotification($walletFundingRequest));
    }

    /**
     * Handle the WalletFundingRequest "updating" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function updating(WalletFundingRequest $walletFundingRequest)
    {
        //
    }

    /**
     * Handle the WalletFundingRequest "updated" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function updated(WalletFundingRequest $walletFundingRequest)
    {
        $user = $walletFundingRequest->user;

        if ($walletFundingRequest->status === WalletFundingStatuses::CLOSED) {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminWalletFundingRequestClosedNotification($walletFundingRequest));

            $user->notify(new WalletFundingRequestClosedNotification());
        }

        if ($walletFundingRequest->status === WalletFundingStatuses::APPROVED) {
            $user->notify(new WalletFundingRequestApprovedNotification($walletFundingRequest));

            event(new WalletHistoryRecorder(
                $user,
                $walletFundingRequest,
                "₦{$walletFundingRequest->amount} added to your wallet"
            ));
        }

        if ($walletFundingRequest->status === WalletFundingStatuses::DECLINED) {
            $user->notify(new WalletFundingRequestDeclinedNotification($walletFundingRequest));
        }
    }

    /**
     * Handle the WalletFundingRequest "deleting" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function deleting(WalletFundingRequest $walletFundingRequest)
    {
        //
    }

    /**
     * Handle the WalletFundingRequest "deleted" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function deleted(WalletFundingRequest $walletFundingRequest)
    {
        //
    }

    /**
     * Handle the WalletFundingRequest "restored" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function restored(WalletFundingRequest $walletFundingRequest)
    {
        //
    }

    /**
     * Handle the WalletFundingRequest "force deleted" event.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function forceDeleted(WalletFundingRequest $walletFundingRequest)
    {
        //
    }
}
