<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartmentLocation extends Model
{
    use HasFactory;
    use UUID;
    use MorphMapTrait;

    /**
     * The attributes that determines verification.
     *
     * @var array<int, string>
     */
    public $verifiableAttributes = [
        'state_id',
        'city_id',
        'local_government_id',
        'house_number',
        'street',
        'landmark',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'state',
        'city',
        'localGovernment',
    ];

    /**
     * Get the apartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    /**
     * Get the state.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the local government.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function localGovernment()
    {
        return $this->belongsTo(LocalGovernment::class);
    }
}
