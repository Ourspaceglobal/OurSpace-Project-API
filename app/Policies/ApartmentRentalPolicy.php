<?php

namespace App\Policies;

use App\Models\Apartment;
use App\Models\ApartmentRental;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApartmentRentalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ApartmentRental $apartmentRental)
    {
        // Use the policy to check if the user is the tenant for this rental
        if ($user->id === $apartmentRental->user_id) {
            return true;
        }

        // Or use it to check if it is the landlord owner of the apartment
        if ($user->id === $apartmentRental->apartment->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @param \App\Models\Apartment $apartment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Apartment $apartment)
    {
        if ($user->id === $apartment->user_id) {
            return false;
        };

        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ApartmentRental $apartmentRental)
    {
        return $user->id === $apartmentRental->user_id;
    }

    /**
     * Determine whether the user can send reminder the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function sendReminder(User $user, ApartmentRental $apartmentRental)
    {
        return $user->id === $apartmentRental->apartment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ApartmentRental $apartmentRental)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param \App\Models\ApartmentRental $apartmentRental
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ApartmentRental $apartmentRental)
    {
        //
    }
}
