<?php

namespace App\Events\Admin;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

class Registered
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $resetLink
     * @return void
     */
    public function __construct(public Authenticatable $user, public string $resetLink)
    {
        //
    }
}
