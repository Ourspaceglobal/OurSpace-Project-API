<?php

namespace App\Listeners\User;

use App\Notifications\User\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {
        $event->user->notify(new WelcomeNotification());
    }
}
