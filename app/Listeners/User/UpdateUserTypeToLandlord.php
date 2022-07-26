<?php

namespace App\Listeners\User;

use App\Enums\UserType;
use App\Events\User\ChangeTenantToLandlord;

class UpdateUserTypeToLandlord
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
     * @param \App\Events\User\ChangeTenantToLandlord $event
     * @return void
     */
    public function handle(ChangeTenantToLandlord $event)
    {
        $user = $event->user;
        $user->type = UserType::LANDLORD;
        $user->save();
    }
}
