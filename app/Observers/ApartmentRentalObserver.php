<?php

namespace App\Observers;

use App\Console\Commands\UpdateApartmentRentalAvailabilityCommand;
use App\Events\User\TempWalletModifier;
use App\Models\Admin;
use App\Models\ApartmentRental;
use App\Notifications\Admin\ApartmentBookingNotification as AdminApartmentBookingNotification;
use App\Notifications\Admin\ApartmentRentalCheckingDateNotification as AdminApartmentRentalCheckingDateNotification;
use App\Notifications\Admin\ApartmentRentalNotification as AdminApartmentRentalNotification;
use App\Notifications\Admin\ApartmentRentalTerminationNotification as AdminApartmentRentalTerminationNotification;
use App\Notifications\User\ApartmentBookingNotification;
use App\Notifications\User\ApartmentRentalCheckingDateNotification;
use App\Notifications\User\ApartmentRentalNotification;
use App\Notifications\User\ApartmentRentalTerminationNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class ApartmentRentalObserver
{
    /**
     * Handle the ApartmentRental "creating" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function creating(ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Handle the ApartmentRental "created" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function created(ApartmentRental $apartmentRental)
    {
        Artisan::call(UpdateApartmentRentalAvailabilityCommand::class, [
            'apartment' => $apartmentRental->apartment->id,
        ]);

        if ($apartmentRental->started_at->isFuture()) {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminApartmentBookingNotification($apartmentRental));

            $apartmentRental->user->notify(new ApartmentBookingNotification($apartmentRental));
            $apartmentRental->apartment->user->notify(new ApartmentBookingNotification($apartmentRental, true));
        } else {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminApartmentRentalNotification($apartmentRental));

            $apartmentRental->user->notify(new ApartmentRentalNotification($apartmentRental));
            $apartmentRental->apartment->user->notify(new ApartmentRentalNotification($apartmentRental, true));
        }
    }

    /**
     * Handle the ApartmentRental "updating" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function updating(ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Handle the ApartmentRental "updated" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function updated(ApartmentRental $apartmentRental)
    {
        Artisan::call(UpdateApartmentRentalAvailabilityCommand::class, [
            'apartment' => $apartmentRental->apartment->id,
        ]);

        if ($apartmentRental->wasChanged('terminated_at')) {
            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminApartmentRentalTerminationNotification($apartmentRental));

            $apartmentRental->user->notify(new ApartmentRentalTerminationNotification($apartmentRental));
            $apartmentRental->apartment->user->notify(
                new ApartmentRentalTerminationNotification($apartmentRental, true)
            );
        }

        if ($apartmentRental->wasChanged(['check_in_date', 'check_out_date'])) {
            $message = $apartmentRental->wasChanged('check_in_date') ? 'checked into' : 'checked out of';

            $admins = Admin::permission('receive email notifications')->get();
            Notification::send($admins, new AdminApartmentRentalCheckingDateNotification($apartmentRental, $message));

            $apartmentRental->user->notify(
                new ApartmentRentalCheckingDateNotification($apartmentRental, $message)
            );
            $apartmentRental->apartment->user->notify(
                new ApartmentRentalCheckingDateNotification($apartmentRental, $message, true)
            );
        }

        if ($apartmentRental->wasChanged('check_in_date')) {
            event(new TempWalletModifier(
                $apartmentRental->apartment->user,
                $apartmentRental->paymentTransaction()->value('truthy_amount'),
                false
            ));
        }
    }

    /**
     * Handle the ApartmentRental "deleting" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function deleting(ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Handle the ApartmentRental "deleted" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function deleted(ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Handle the ApartmentRental "restored" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function restored(ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Handle the ApartmentRental "force deleted" event.
     *
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return void
     */
    public function forceDeleted(ApartmentRental $apartmentRental)
    {
        //
    }
}
