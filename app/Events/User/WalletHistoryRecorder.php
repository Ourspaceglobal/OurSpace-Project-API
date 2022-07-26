<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WalletHistoryRecorder
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Model $model
     * @param string $log
     * @return void
     */
    public function __construct(public User $user, public Model $model, public string $log)
    {
        //
    }
}
