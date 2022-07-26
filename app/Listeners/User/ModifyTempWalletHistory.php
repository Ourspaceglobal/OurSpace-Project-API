<?php

namespace App\Listeners\User;

use App\Events\User\TempWalletModifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ModifyTempWalletHistory
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
     * @param \App\Events\User\TempWalletModifier $event
     * @return void
     */
    public function handle(TempWalletModifier $event)
    {
        $user = $event->user;
        $amount = $event->amount;

        if ($event->increase) {
            $user->temp_wallet_balance += $amount;
        } else {
            $user->temp_wallet_balance -= $amount;
            $user->wallet_balance += $amount;
        }

        $user->save();
    }
}
