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

class Post extends Model implements HasMedia
{
    use HasFactory;
    use UUID;
    use SoftDeletes;
    use InteractsWithMedia;
    use LogsActivity;
    use MorphMapTrait;
    use Reviewable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret_key',
        'secret_key_last_used',
        'media',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'secret_key_last_used' => 'datetime',
        'is_published' => 'boolean',
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
     * The relations to eager load on every query.
     *
     * @var array<int, string>
     */
    protected $with = [
        'tags',
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
            . " ({$this->title}) was {$eventName} by "
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
     * Get post's featured image.
     *
     * @return string
     */
    public function getFeaturedImageAttribute()
    {
        return $this->getFirstMediaUrl(MediaCollection::FEATUREDIMAGE);
    }

    /**
     * Scope published posts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query, bool $status = true)
    {
        return $query->where('is_published', $status);
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
        return $query->where('title', 'LIKE', "%{$text}%");
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
     * Get all of the tags for the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->select('id', 'name');
    }

    /**
     * Get the comments on the post.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'model');
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
     * Get the post's views.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function views()
    {
        return $this->morphMany(View::class, 'model');
    }
}
