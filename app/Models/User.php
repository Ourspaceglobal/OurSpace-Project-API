<?php

namespace App\Models;

use App\Contracts\CanVerifyEmailWithCode;
use App\Enums\MediaCollection;
use App\Notifications\User\PasswordUpdatedNotification;
use App\Notifications\User\ResetPasswordNotification;
use App\Notifications\User\VerifyEmailNotification;
use App\Traits\EmailVerificationCodeTrait;
use App\Traits\MorphMapTrait;
use App\Traits\Reviewable;
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

class User extends Authenticatable implements MustVerifyEmail, HasMedia, CanVerifyEmailWithCode
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use UUID;
    use InteractsWithMedia;
    use SoftDeletes;
    use LogsActivity;
    use MorphMapTrait;
    use Reviewable;
    use EmailVerificationCodeTrait;

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
        'date_of_birth' => 'date',
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
     * Get user's avatar.
     *
     * @return string
     */
    public function getAvatarAttribute()
    {
        return $this->getFirstMediaUrl(MediaCollection::AVATAR);
    }

    /**
     * Get user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Set the user's email.
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
        $callbackUrl = request('callbackUrl', config('frontend.url'));

        $this->notify(new VerifyEmailNotification($callbackUrl, $this->generateEmailVerificationCode()));
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $callbackUrl = request('callbackUrl', config('frontend.url'));

        // Get the password_resets code
        $code = \Illuminate\Support\Facades\Password::broker()->getRepository()->getUsedTable()
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
        $callbackUrl = request('callbackUrl', config('frontend.url'));

        $this->notify(new PasswordUpdatedNotification($callbackUrl));
    }

    /**
     * Temporary login associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function temporaryLogin()
    {
        return $this->morphOne(TemporaryLogin::class, 'user');
    }

    /**
     * Email verification code associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function emailVerificationCode()
    {
        return $this->morphOne(EmailVerificationCode::class, 'user');
    }

    /**
     * Get the user's bank accounts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get the user's landlord requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function landlordRequests()
    {
        return $this->hasMany(LandlordRequest::class);
    }

    /**
     * Get the user's support tickets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get the user's withdrawal requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    /**
     * Get the user's wallet funding requests.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function walletFundingRequests()
    {
        return $this->hasMany(WalletFundingRequest::class);
    }

    /**
     * Get the user's wallet history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function walletHistory()
    {
        return $this->hasMany(WalletHistory::class);
    }

    /**
     * Get the user's apartments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    /**
     * Get the favorites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorites()
    {
        return $this->belongsToMany(Apartment::class, 'apartment_favorites')->withTimestamps();
    }

    /**
     * Get the bookings.
     *
     * @return mixed
     */
    public function apartmentBookings()
    {
        return $this->apartmentRentals()->bookings();
    }

    /**
     * Get the payment cards.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function paymentCards()
    {
        return $this->morphMany(PaymentCard::class, 'user');
    }

    /**
     * Get the payment cards.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function paymentTransactions()
    {
        return $this->morphMany(PaymentTransaction::class, 'user');
    }

    /**
     * Get the rentals paid by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apartmentRentals()
    {
        return $this->hasMany(ApartmentRental::class);
    }

    /**
     * Get the rentals owned by the user (landlord).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function myApartmentRentals()
    {
        return $this->hasManyThrough(ApartmentRental::class, Apartment::class);
    }

    /**
     * Get the apartment kycs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apartmentKycs()
    {
        return $this->hasMany(UserApartmentKyc::class);
    }

    /**
     * Get the user's written reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the user's received reviews.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function receivedReviews()
    {
        return $this->morphMany(Review::class, 'model');
    }

    /**
     * Get the user's views.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function views()
    {
        return $this->hasMany(View::class);
    }
}
