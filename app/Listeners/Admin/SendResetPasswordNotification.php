<?php

namespace App\Listeners\Admin;

use App\Events\Admin\ConfirmResetPassword;
use App\Notifications\Admin\ResetPasswordConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendResetPasswordNotification
{
    /**
     * Handle the event.
     *
     * @param \App\Events\Admin\ConfirmResetPassword $event
     * @return void
     */
    public function handle(ConfirmResetPassword $event)
    {
        $event->user->notify(new ResetPasswordConfirmedNotification($event->callbackUrl));
    }
}
