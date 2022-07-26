<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    use UUID;
    use MorphMapTrait;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot',
    ];

    /**
     * Get all of the posts that are assigned this tag.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }
}
