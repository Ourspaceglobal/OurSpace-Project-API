<?php

namespace App\Models;

use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ApartmentRental extends Model
{
    use HasFactory;
    use UUID;
    use LogsActivity;
    use MorphMapTrait;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'expired_at' => 'datetime',
        'terminated_at' => 'datetime',
        'is_autorenewal_active' => 'boolean',
        'last_reminder_sent_at' => 'datetime',
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
    ];

    /**
     * Indicates custom attributes to append to model.
     *
     * @var array<int, string>
     */
    public $appends = [
        'is_active',
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
            . " for {$this->apartment->name} was {$eventName} by "
            . (
                $activity->causer
                    ? ("{$activity->causer->full_name} [$activity->causer_type]")
                    : "system"
            );
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
     * Get the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentTransaction()
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    /**
     * Scope only future (bookings) records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBookings($query, bool $value = true)
    {
        return $query->when(
            $value,
            fn () => $query->where('started_at', '>=', now()),
            fn () => $query->where('started_at', '<=', now())
        );
    }

    /**
     * Scope only active records
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('started_at', '<=', now())
            ->where('expired_at', '>=', now())
            ->whereNull('terminated_at')
            ->whereNull('check_out_date');
    }

    /**
     * Scope only inactive records.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotActive($query)
    {
        return $query->where('started_at', '>=', now())
            ->orWhere('expired_at', '<=', now())
            ->orWhereNotNull('terminated_at')
            ->orWhereNotNull('check_out_date');
    }

    /**
     * Get the active status of the rental.
     *
     * @return Attribute
     */
    public function isActive(): Attribute
    {
        return new Attribute(
            get: fn () => $this->started_at <= now()
                && $this->expired_at >= now()
                && $this->terminated_at === null
                && $this->check_out_date === null,
        );
    }
}
