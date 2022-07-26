<?php

namespace App\Observers;

use App\Enums\WithdrawalRequestStatuses;
use App\Events\User\WalletHistoryRecorder;
use App\Models\Admin;
use App\Models\WithdrawalRequest;
use App\Notifications\Admin\WithdrawalRequestClosedNotification as AdminWithdrawalRequestClosedNotification;
use App\Notifications\Admin\WithdrawalRequestSubmittedNotification as AdminWithdrawalRequestSubmittedNotification;
use App\Notifications\User\WithdrawalRequestApprovedNotification;
use App\Notifications\User\WithdrawalRequestClosedNotification;
use App\Notifications\User\WithdrawalRequestDeclinedNotification;
use App\Notifications\User\WithdrawalRequestSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class WithdrawalRequestObserver
{
    /**
     * Handle the WithdrawalRequest "creating" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function creating(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Handle the WithdrawalRequest "created" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function created(WithdrawalRequest $withdrawalRequest)
    {
        $admins = Admin::permission('receive email notifications')->get();
        Notification::send($admins, new AdminWithdrawalRequestSubmittedNotification($withdrawalRequest));

        $withdrawalRequest->user->notify(new WithdrawalRequestSubmittedNotification($withdrawalRequest));
    }

    /**
     * Handle the WithdrawalRequest "updating" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function updating(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Handle the WithdrawalRequest "updated" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function updated(WithdrawalRequest $withdrawalRequest)
    {
        $user = $withdrawalRequest->user;

        if ($withdrawalRequest->status === WithdrawalRequestStatuses::CLOSED) {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminWithdrawalRequestClosedNotification($withdrawalRequest));

            $user->notify(new WithdrawalRequestClosedNotification());
        }

        if ($withdrawalRequest->status === WithdrawalRequestStatuses::APPROVED) {
            $user->notify(new WithdrawalRequestApprovedNotification($withdrawalRequest));

            event(new WalletHistoryRecorder(
                $user,
                $withdrawalRequest,
                "₦{$withdrawalRequest->amount} deducted from your wallet"
            ));
        }

        if ($withdrawalRequest->status === WithdrawalRequestStatuses::DECLINED) {
            $user->notify(new WithdrawalRequestDeclinedNotification($withdrawalRequest));
        }
    }

    /**
     * Handle the WithdrawalRequest "deleting" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function deleting(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Handle the WithdrawalRequest "deleted" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function deleted(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Handle the WithdrawalRequest "restored" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function restored(WithdrawalRequest $withdrawalRequest)
    {
        //
    }

    /**
     * Handle the WithdrawalRequest "force deleted" event.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function forceDeleted(WithdrawalRequest $withdrawalRequest)
    {
        //
    }
}
