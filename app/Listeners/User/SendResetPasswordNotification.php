<?php

namespace App\Listeners\User;

use App\Events\User\ConfirmResetPassword;
use App\Notifications\User\ResetPasswordConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendResetPasswordNotification
{
    /**
     * Handle the event.
     *
     * @param \App\Events\User\ConfirmResetPassword $event
     * @return void
     */
    public function handle(ConfirmResetPassword $event)
    {
        $event->user->notify(new ResetPasswordConfirmedNotification($event->callbackUrl));
    }
}
