<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Traits\MorphMapTrait;
use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupportTicketReply extends Model implements HasMedia
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
     * Get the user (User or Admin)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Get the parent ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }
}
