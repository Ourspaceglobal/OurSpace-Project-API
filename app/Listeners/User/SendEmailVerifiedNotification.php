<?php

namespace App\Listeners\User;

use App\Notifications\User\EmailVerifiedNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerifiedNotification
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Verified $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $event->user->emailVerificationCode()->delete();

        $event->user->notify(new EmailVerifiedNotification());
    }
}
