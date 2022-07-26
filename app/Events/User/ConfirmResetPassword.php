<?php

namespace App\Events\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConfirmResetPassword
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string $callbackUrl
     */
    public function __construct(public Authenticatable $user, public string $callbackUrl)
    {
        $this->user = $user;
        $this->callbackUrl = $callbackUrl;
    }
}
