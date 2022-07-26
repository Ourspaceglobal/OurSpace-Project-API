<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApartmentDuration extends Model
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use MorphMapTrait;

    /**
     * Mutate the period column.
     *
     * @return Attribute
     */
    protected function period(): Attribute
    {
        return new Attribute(
            get: fn($value) => strtolower($value),
            set: fn($value) => strtolower($value),
        );
    }

    /**
     * Get the apartments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }
}
