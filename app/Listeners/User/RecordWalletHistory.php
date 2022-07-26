<?php

namespace App\Listeners\User;

use App\Events\User\WalletHistoryRecorder;
use App\Models\WalletHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RecordWalletHistory implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\User\WalletHistoryRecorder $event
     * @return void
     */
    public function handle(WalletHistoryRecorder $event)
    {
        $walletHistory = new WalletHistory();
        $walletHistory->user()->associate($event->user);
        $walletHistory->model()->associate($event->model);
        $walletHistory->log = $event->log;
        $walletHistory->save();
    }
}
