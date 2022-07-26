<?php

namespace App\Listeners\Admin;

use App\Events\Admin\Registered;
use App\Notifications\Admin\WelcomeNotification;

class SendWelcomeNotification
{
    /**
     * Handle the event.
     *
     * @param Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $event->user->notify(new WelcomeNotification($event->resetLink));
    }
}
