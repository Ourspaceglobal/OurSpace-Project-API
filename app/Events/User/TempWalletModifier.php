<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TempWalletModifier
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param float $amount
     * @param bool $increase
     * @return void
     */
    public function __construct(
        public User $user,
        public float $amount,
        public bool $increase = true
    ) {
        //
    }
}
