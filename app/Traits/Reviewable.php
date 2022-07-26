<?php

namespace App\Traits;

trait Reviewable
{
    /**
     * Update rating for the model.
     *
     * @return void
     */
    public function updateRating()
    {
        try {
            $this->rating = $this->reviews()->approved()->average('rating');
            $this->saveQuietly();
        } catch (\PDOException $e) {
            return;
        }
    }
}
