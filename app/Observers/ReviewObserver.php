<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    /**
     * Handle the Review "creating" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function creating(Review $review)
    {
        //
    }

    /**
     * Handle the Review "created" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function created(Review $review)
    {
        if (method_exists($review->model, 'updateRating')) {
            $review->model->updateRating();
        }
    }

    /**
     * Handle the Review "updating" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function updating(Review $review)
    {
        //
    }

    /**
     * Handle the Review "updated" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function updated(Review $review)
    {
        if (method_exists($review->model, 'updateRating')) {
            $review->model->updateRating();
        }
    }

    /**
     * Handle the Review "deleting" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function deleting(Review $review)
    {
        //
    }

    /**
     * Handle the Review "deleted" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function deleted(Review $review)
    {
        //
    }

    /**
     * Handle the Review "restored" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function restored(Review $review)
    {
        //
    }

    /**
     * Handle the Review "force deleted" event.
     *
     * @param \App\Models\Review $review
     * @return void
     */
    public function forceDeleted(Review $review)
    {
        //
    }
}
