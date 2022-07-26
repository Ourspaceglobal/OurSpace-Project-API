<?php

namespace App\Listeners\User;

use App\Events\User\ViewLogger;
use App\Models\View;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogView implements ShouldQueue
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
     * @param \App\Events\User\ViewLogger $event
     * @return void
     */
    public function handle(ViewLogger $event)
    {
        $user = $event->user;
        $model = $event->model;

        $view = new View();
        $view->user_id = $user?->id;
        $view->model()->associate($model);
        $view->save();
    }
}
