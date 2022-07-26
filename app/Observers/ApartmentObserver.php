<?php

namespace App\Observers;

use App\Models\Apartment;
use App\Notifications\User\ApartmentPriceUpdateNotification;
use App\Notifications\User\ApartmentUpdatedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ApartmentObserver
{
    /**
     * Handle the Apartment "creating" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function creating(Apartment $apartment)
    {
        $apartment->slug = Str::slug($apartment->name . '-' . head(explode('-', $apartment->id)));
    }

    /**
     * Handle the Apartment "created" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function created(Apartment $apartment)
    {
        //
    }

    /**
     * Handle the Apartment "updating" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function updating(Apartment $apartment)
    {
        if ($apartment->isDirty($apartment->verifiableAttributes)) {
            $apartment->is_verified = false;
        }

        if ($apartment->isDirty('name')) {
            $apartment->slug = Str::slug($apartment->name . '-' . head(explode('-', $apartment->id)));
        }

        if ($apartment->isDirty('is_active')) {
            $apartment->activated_at = $apartment->is_active ? now() : null;
        }

        if ($apartment->isDirty('is_verified')) {
            $apartment->verified_at = $apartment->is_verified ? now() : null;
        }

        if ($apartment->isDirty('is_featured')) {
            $apartment->featured_at = $apartment->is_featured ? now() : null;
        }
    }

    /**
     * Handle the Apartment "updated" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function updated(Apartment $apartment)
    {
        // Notify all users who have active rents on the apartment that the price has changed
        if ($apartment->wasChanged('price') && $apartment->rentals()->active()->exists()) {
            $users = \App\Models\User::whereIn('id', $apartment->rentals()->active()->pluck('user_id'))->get();
            Notification::send($users, new ApartmentPriceUpdateNotification($apartment));
        }

        if ($apartment->wasChanged($apartment->verifiableAttributes)) {
            $apartment->user->notify(new ApartmentUpdatedNotification(
                $apartment,
                'updated'
            ));
        }

        if ($apartment->wasChanged('is_active')) {
            $apartment->user->notify(new ApartmentUpdatedNotification(
                $apartment,
                ($apartment->is_active ? '' : 'un') . 'published'
            ));
        }

        if ($apartment->wasChanged('is_verified')) {
            $apartment->user->notify(new ApartmentUpdatedNotification(
                $apartment,
                ($apartment->is_verified ? '' : 'un') . 'verified'
            ));
        }

        if ($apartment->wasChanged('is_featured')) {
            $apartment->user->notify(new ApartmentUpdatedNotification(
                $apartment,
                ($apartment->is_featured ? '' : 'un') . 'featured'
            ));
        }
    }

    /**
     * Handle the Apartment "deleting" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function deleting(Apartment $apartment)
    {
        //
    }

    /**
     * Handle the Apartment "deleted" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function deleted(Apartment $apartment)
    {
        //
    }

    /**
     * Handle the Apartment "restored" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function restored(Apartment $apartment)
    {
        //
    }

    /**
     * Handle the Apartment "force deleted" event.
     *
     * @param \App\Models\Apartment $apartment
     * @return void
     */
    public function forceDeleted(Apartment $apartment)
    {
        //
    }
}
