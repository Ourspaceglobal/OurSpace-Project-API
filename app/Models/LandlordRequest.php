<?php

namespace App\Models;

use App\Enums\LandlordRequestStatuses;
use App\Enums\MediaCollection;
use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LandlordRequest extends Model implements HasMedia
{
    use HasFactory;
    use UUID;
    use InteractsWithMedia;
    use LogsActivity;
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_declined' => 'boolean',
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
            . " was {$eventName} by "
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
        $this->addMediaCollection(MediaCollection::KYC)->onlyKeepLatest(5);
    }

    /**
     * Get KYCs documents.
     *
     * @return MediaCollections\Models\Collections\MediaCollection
     */
    public function kycs()
    {
        return $this->getMedia(MediaCollection::KYC);
    }

    /**
     * Get the user (tenant).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pending requests.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', LandlordRequestStatuses::PENDING);
    }
}
