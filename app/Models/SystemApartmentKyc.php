<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SystemApartmentKyc extends Model
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use LogsActivity;
    use MorphMapTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
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
     * Get the datatype.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function datatype()
    {
        return $this->belongsTo(Datatype::class);
    }

    /**
     * Scope only required records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRequired($query, $value = true)
    {
        return $query->where('is_required', $value);
    }
}
