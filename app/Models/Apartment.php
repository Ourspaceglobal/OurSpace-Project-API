<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\MorphMapTrait;
use App\Traits\Reviewable;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Apartment extends Model implements HasMedia
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use InteractsWithMedia;
    use LogsActivity;
    use MorphMapTrait;
    use Reviewable;

    /**
     * The attributes that determines verification.
     *
     * @var array<int, string>
     */
    public $verifiableAttributes = [
        'category_id',
        'sub_category_id',
        'apartment_duration_id',
        'name',
        'description',
        'price',
        'featured_image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'media',
        'unavailable_booking_dates',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'verified_at' => 'datetime',
        'featured_at' => 'datetime',
        'available_for_rent' => 'boolean',
        'unavailable_booking_dates' => 'array',
    ];

    /**
     * Indicates custom attributes to append to model.
     *
     * @var array<int, string>
     */
    public $appends = [
        'featured_image',
    ];

    /**
     * Get activity log options.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName($this->getMorphClass())
            ->dontSubmitEmptyLogs();
    }

    /**
     * Customize the activity log before it is saved.
     *
     * @param Activity $activity
     * @param string $eventName
     * @return void
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = \Illuminate\Support\Str::headline((new \ReflectionClass($this))->getShortName())
            . " ({$this->name}) was {$eventName} by "
            . (
                $activity->causer
                    ? ("{$activity->causer->full_name} [$activity->causer_type]")
                    : "system"
            );
    }

    /**
     * Define the user media collections.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::FEATUREDIMAGE)->singleFile();
    }

    /**
     * Get apartment's featured.
     *
     * @return string
     */
    public function getFeaturedImageAttribute()
    {
        return $this->getFirstMediaUrl(MediaCollection::FEATUREDIMAGE);
    }

    /**
     * Get the booking dates that are unavailable for the apartment.
     *
     * @return array<int, Carbon::toDateString()>
     */
    public function getUnavailableBookingDates()
    {
        $dates = collect();

        $this->bookings()
            ->select(['id', 'apartment_id', 'started_at', 'expired_at'])
            ->orderBy('started_at')
            ->get()
            ->each(function ($booking) use ($dates) {
                $from = $booking->started_at;
                $to = $booking->expired_at;

                $dates->push($from->range($to)->toArray());
            });

        $dates = $dates->flatten()->sort()->map(fn ($date) => now()->parse($date)->toDateString());

        return $dates->toArray();
    }

    /**
     * Scope to only include active records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to only include verified records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to only include featured records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query, bool $value = true)
    {
        return $query->where('is_featured', $value);
    }

    /**
     * Scope a query to filter results based on full text matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $text
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeFullText($query, string $text)
    {
        return $query->where('name', 'LIKE', "%{$text}%")
            ->orWhereHas('location', fn ($query) => $query->where('full_address', 'LIKE', "%{$text}%"));
    }

    /**
     * Scope a query to filter results based on rentals - must not have any active rental.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeNoActiveRentals($query)
    {
        return $query->where('available_for_rent', true);
    }

    /**
     * Scope a query to filter results based on rentals - must have any active rental.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActiveRentals($query)
    {
        return $query->whereHas('rentals', fn ($query) => $query->active());
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
     * Get the category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the sub-category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    /**
     * Get the apartment duration.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function apartmentDuration()
    {
        return $this->belongsTo(ApartmentDuration::class);
    }

    /**
     * Get the amenities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'apartment_amenity')->withPivot('total_number')->withTimestamps();
    }

    /**
     * Get the favorites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites()
    {
        return $this->belongsToMany(User::class, 'apartment_favorites')->withTimestamps();
    }

    /**
     * Get the contact info for the apartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function contact()
    {
        return $this->hasOne(ApartmentContact::class);
    }

    /**
     * Get the location data for the apartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function location()
    {
        return $this->hasOne(ApartmentLocation::class);
    }

    /**
     * Get the galleries for the apartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function galleries()
    {
        return $this->hasMany(ApartmentGallery::class);
    }

    /**
     * Get the bookings for the apartment.
     *
     * @return mixed
     */
    public function bookings()
    {
        return $this->rentals()->bookings();
    }

    /**
     * Get the rentals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rentals()
    {
        return $this->hasMany(ApartmentRental::class);
    }

    /**
     * Get the custom apartment KYCs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function customApartmentKycs()
    {
        return $this->belongsToMany(SystemApartmentKyc::class, 'custom_apartment_kycs')->withTimestamps();
    }

    /**
     * Get the reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function reviews()
    {
        return $this->morphMany(Review::class, 'model');
    }

    /**
     * Get the apartment's views.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function views()
    {
        return $this->morphMany(View::class, 'model');
    }
}
