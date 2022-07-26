<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportTicket extends Model implements HasMedia
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_open' => 'boolean',
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array<int, string>
     */
    protected $withCount = [
        'replies',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'is_awaiting_reply',
    ];

    /**
     * Get the awaiting reply status of the support ticket.
     *
     * @return Attribute
     */
    public function isAwaitingReply(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_open
                && (
                    $this->replies()->count() === 0
                    || $this->replies()->latest()->limit(1)->value('user_type') === 'user'
                ),
        );
    }

    /**
     * Define the user media collections.
     *
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::ATTACHMENT)->onlyKeepLatest(5);
    }

    /**
     * Get attachments.
     *
     * @return MediaCollections\Models\Collections\MediaCollection
     */
    public function attachments()
    {
        return $this->getMedia(MediaCollection::ATTACHMENT);
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
     * Get the replies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(SupportTicketReply::class);
    }
}
