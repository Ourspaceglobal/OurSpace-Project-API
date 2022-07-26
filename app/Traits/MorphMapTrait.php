<?php

namespace App\Traits;

trait MorphMapTrait
{
    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return \Illuminate\Support\Str::snake((new \ReflectionClass($this))->getShortName());
    }
}
