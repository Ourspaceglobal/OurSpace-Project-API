<?php

namespace App\Events\User;

use App\Models\Apartment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApartmentVerification
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Apartment $apartment
     * @param bool $verify
     * @return void
     */
    public function __construct(public Apartment $apartment, public bool $verify = false)
    {
        //
    }
}
