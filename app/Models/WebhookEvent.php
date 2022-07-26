<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;
    use UUID;
    use MorphMapTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'log' => 'array',
    ];
}
