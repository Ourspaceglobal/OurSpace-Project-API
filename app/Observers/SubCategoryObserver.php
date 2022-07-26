<?php

namespace App\Observers;

use App\Models\SubCategory;
use Illuminate\Support\Str;

class SubCategoryObserver
{
    /**
     * Handle the SubCategory "creating" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function creating(SubCategory $subCategory)
    {
        $subCategory->slug = Str::slug($subCategory->name . '-' . head(explode('-', $subCategory->id)));
    }

    /**
     * Handle the SubCategory "created" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function created(SubCategory $subCategory)
    {
        //
    }

    /**
     * Handle the SubCategory "updating" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function updating(SubCategory $subCategory)
    {
        if ($subCategory->isDirty('name')) {
            $subCategory->slug = Str::slug($subCategory->name . '-' . head(explode('-', $subCategory->id)));
        }
    }

    /**
     * Handle the SubCategory "updated" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function updated(SubCategory $subCategory)
    {
        //
    }

    /**
     * Handle the SubCategory "deleting" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function deleting(SubCategory $subCategory)
    {
        //
    }

    /**
     * Handle the SubCategory "deleted" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function deleted(SubCategory $subCategory)
    {
        //
    }

    /**
     * Handle the SubCategory "restored" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function restored(SubCategory $subCategory)
    {
        //
    }

    /**
     * Handle the SubCategory "force deleted" event.
     *
     * @param \App\Models\SubCategory $subCategory
     * @return void
     */
    public function forceDeleted(SubCategory $subCategory)
    {
        //
    }
}
