<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TemporaryLogin extends model
{
    use UUID;
    use MorphMapTrait;

    public const EXPIRATION_TIME_IN_MINUTES = 5;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(fn ($model) => $model->code = $model->generateCode());
    }

    /**
     * Authentication providers associated with the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Gets the expiration state of the code
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->created_at->diffInMinutes(Carbon::now()) > static::EXPIRATION_TIME_IN_MINUTES;
    }

    /**
     * Generate a six digits code
     *
     * @param int $codeLength
     * @return string
     */
    public function generateCode()
    {
        return mt_rand(100000, 999999);
    }

    /**
     * Scope a query to only include records that belong to a user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\User $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfUser($query, $user)
    {
        return $query->whereHasMorph(
            'user',
            [User::class],
            fn (Builder $query) => $query->where('id', $user->id)
        );
    }

    /**
     * Scope a query to only include records that belong to an admin.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \App\Models\Admin $admin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfAdmin($query, $admin)
    {
        return $query->whereHasMorph(
            'user',
            [Admin::class],
            fn (Builder $query) => $query->where('id', $admin->id)
        );
    }
}
