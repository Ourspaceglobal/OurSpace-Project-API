<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ViewLogger
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The viewed model.
     *
     * @var Model
     */
    public Model $model;

    /**
     * The user that viewed the model.
     *
     * @var User|null
     */
    public User|null $user;

    /**
     * Create a new event instance.
     *
     * @param Model $model
     * @param User|null $user
     * @return void
     */
    public function __construct(Model $model, User $user = null)
    {
        $this->model = $model;
        $this->user = $user;
    }
}
