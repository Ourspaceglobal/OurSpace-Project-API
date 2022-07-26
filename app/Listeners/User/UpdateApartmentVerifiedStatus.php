<?php

namespace App\Listeners\User;

use App\Events\User\ApartmentVerification;

class UpdateApartmentVerifiedStatus
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
     * @param \App\Events\User\ApartmentVerification $event
     * @return void
     */
    public function handle(ApartmentVerification $event)
    {
        $apartment = $event->apartment;
        $apartment->is_verified = $event->verify;
        $apartment->save();
    }
}
