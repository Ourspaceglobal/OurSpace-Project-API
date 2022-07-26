<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UserApartmentKyc extends Model implements HasMedia
{
    use HasFactory;
    use UUID;
    use InteractsWithMedia;
    use MorphMapTrait;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'media',
    ];

    /**
     * Define the user media collections.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::KYC)->singleFile();
    }

    /**
     * Get the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

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
     * Get the system apartment KYC.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function systemApartmentKyc()
    {
        return $this->belongsTo(SystemApartmentKyc::class);
    }
}
