<?php

namespace App\Listeners\Admin;

use App\Events\Admin\Verified;
use App\Notifications\Admin\EmailVerifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendEmailVerifiedNotification
{
    /**
     * Handle the event.
     *
     * @param \App\Events\Admin\Verified $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $event->user->notify(new EmailVerifiedNotification());
    }
}
