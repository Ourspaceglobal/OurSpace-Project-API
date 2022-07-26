<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use MorphMapTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_approved' => 'boolean',
    ];

    /**
     * Scope a query to only include approved comments.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Get the user [User or Admin]
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Get the model [Post]
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function model()
    {
        return $this->morphTo();
    }

    /**
     * Get the comment's replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('updated_at', 'desc');
    }
}
