<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Datatype extends Model
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use MorphMapTrait;
}
