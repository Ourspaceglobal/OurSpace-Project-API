<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailVerificationCode extends Model
{
    use HasFactory;
    use UUID;
    use MorphMapTrait;

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
     * Get the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Determine if code has expired.
     *
     * @return boolean
     */
    public function getIsExpiredAttribute()
    {
        return $this->created_at->diffInMinutes(now()) > config('auth.verification.expire', 60);
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
}
