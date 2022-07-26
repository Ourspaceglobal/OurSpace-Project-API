<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentContact extends Model
{
    use HasFactory;
    use UUID;
    use MorphMapTrait;

    /**
     * Get the apartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }
}
