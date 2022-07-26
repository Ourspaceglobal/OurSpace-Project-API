<?php

namespace App\Observers;

use App\Events\User\ApartmentVerification;
use App\Jobs\PopulateGeocodeForAddress;
use App\Models\ApartmentLocation;

class ApartmentLocationObserver
{
    /**
     * Handle the ApartmentLocation "creating" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function creating(ApartmentLocation $apartmentLocation)
    {
        $apartmentLocation->full_address = $apartmentLocation->house_number
            . ($apartmentLocation->street ? ", {$apartmentLocation->street}" : '')
            . ($apartmentLocation->landmark ? ", {$apartmentLocation->landmark}" : '')
            . ($apartmentLocation->localGovernment ? ", {$apartmentLocation->localGovernment->name}" : '')
            . ($apartmentLocation->city ? ", {$apartmentLocation->city->name}" : '')
            . ($apartmentLocation->state ? ", {$apartmentLocation->state->name}" : '')
        ;
    }

    /**
     * Handle the ApartmentLocation "created" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function created(ApartmentLocation $apartmentLocation)
    {
        event(new ApartmentVerification($apartmentLocation->apartment));

        PopulateGeocodeForAddress::dispatch($apartmentLocation);
    }

    /**
     * Handle the ApartmentLocation "updating" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function updating(ApartmentLocation $apartmentLocation)
    {
        $apartmentLocation->full_address = $apartmentLocation->house_number
            . ($apartmentLocation->street ? ", {$apartmentLocation->street}" : '')
            . ($apartmentLocation->landmark ? ", {$apartmentLocation->landmark}" : '')
            . ($apartmentLocation->localGovernment ? ", {$apartmentLocation->localGovernment->name}" : '')
            . ($apartmentLocation->city ? ", {$apartmentLocation->city->name}" : '')
            . ($apartmentLocation->state ? ", {$apartmentLocation->state->name}" : '')
        ;
    }

    /**
     * Handle the ApartmentLocation "updated" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function updated(ApartmentLocation $apartmentLocation)
    {
        if ($apartmentLocation->wasChanged($apartmentLocation->verifiableAttributes)) {
            event(new ApartmentVerification($apartmentLocation->apartment));
        }

        PopulateGeocodeForAddress::dispatch($apartmentLocation);
    }

    /**
     * Handle the ApartmentLocation "deleting" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function deleting(ApartmentLocation $apartmentLocation)
    {
        //
    }

    /**
     * Handle the ApartmentLocation "deleted" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function deleted(ApartmentLocation $apartmentLocation)
    {
        //
    }

    /**
     * Handle the ApartmentLocation "restored" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function restored(ApartmentLocation $apartmentLocation)
    {
        //
    }

    /**
     * Handle the ApartmentLocation "force deleted" event.
     *
     * @param \App\Models\ApartmentLocation $apartmentLocation
     * @return void
     */
    public function forceDeleted(ApartmentLocation $apartmentLocation)
    {
        //
    }
}
