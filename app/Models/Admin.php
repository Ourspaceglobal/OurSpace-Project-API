<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Notifications\Admin\PasswordUpdatedNotification;
use App\Notifications\Admin\ResetPasswordNotification;
use App\Notifications\Admin\VerifyEmailNotification;
use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements MustVerifyEmail, HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use UUID;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasRoles;
    use LogsActivity;
    use MorphMapTrait;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'media',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_blocked' => 'boolean',
        'is_2fa_active' => 'boolean',
    ];

    /**
     * Indicates custom attributes to append to model.
     *
     * @var array<int, string>
     */
    public $appends = [
        'avatar',
        'full_name',
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
            . " ({$this->full_name}) was {$eventName} by "
            . (
                $activity->causer
                    ? ("{$activity->causer->full_name} [$activity->causer_type]")
                    : "system"
            );

        if ($properties = $activity->properties) {
            if ($properties->has('attributes')) {
                $attributes = $properties->get('attributes');
                if (isset($attributes['password'])) {
                    $attributes['password'] = '<newsecret>';
                }
                $properties->put('attributes', $attributes);
            }
            if ($properties->has('old')) {
                $old = $properties->get('old');
                if (isset($old['password'])) {
                    $old['password'] = '<oldsecret>';
                }
                $properties->put('old', $old);
            }
            $activity->properties = $properties;
        }
    }

    /**
     * Define the user media collections.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::AVATAR)
            ->useFallbackUrl(url('/images/avatar-placeholder.jpg'))
            ->singleFile();
    }

    /**
     * Get admin's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->getFirstMediaUrl(MediaCollection::AVATAR);
    }

    /**
     * Get admin's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Set the admin's email.
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $callbackUrl = request('callbackUrl', config('frontend.admin.url'));

        $this->notify(new VerifyEmailNotification($callbackUrl));
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $callbackUrl = request('callbackUrl', config('frontend.admin.url'));

        // Get the password_resets code
        $code = \Illuminate\Support\Facades\Password::broker('admins')->getRepository()->getUsedTable()
            ->where('email', $this->getEmailForPasswordReset())
            ->value('code');

        $this->notify(new ResetPasswordNotification($token, $code, $callbackUrl));
    }

    /**
     * Send the password update notification.
     *
     * @return void
     */
    public function sendPasswordUpdatedNotification()
    {
        $callbackUrl = request('callbackUrl', config('frontend.admin.url'));

        $this->notify(new PasswordUpdatedNotification($callbackUrl));
    }

    /**
     * Scope a query to filter results based on full name matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeFullName($query, string $name)
    {
        return $query->where('first_name', 'LIKE', "%{$name}%")->orWhere('last_name', 'LIKE', "%{$name}%");
    }

    /**
     * Temporary login associated with the admin.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function temporaryLogin()
    {
        return $this->morphOne(TemporaryLogin::class, 'user');
    }

    /**
     * Get the admin's posts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
