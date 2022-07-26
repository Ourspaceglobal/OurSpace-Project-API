<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PushNotification extends Model
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
        'send_at' => 'datetime',
        'send_via_mail' => 'boolean',
        'send_via_system' => 'boolean',
        'is_sent' => 'boolean',
        'user_ids' => AsArrayObject::class,
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
     * Get the admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Get the users to notify.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function users()
    {
        return $this->user_ids[0] === '*'
            ? 'ALL'
            : User::query()->select(['id', 'first_name', 'last_name', 'email'])->whereIn('id', $this->user_ids)->get();
    }

    /**
     * Scope unsent notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }
}
