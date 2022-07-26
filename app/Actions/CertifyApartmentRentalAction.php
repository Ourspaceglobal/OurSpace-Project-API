<?php

namespace App\Actions;

use App\Exceptions\ApartmentRentalCertificationException;
use App\Models\Apartment;
use App\Models\SystemApartmentKyc;
use App\Models\User;
use Spatie\QueueableAction\QueueableAction;

class CertifyApartmentRentalAction
{
    use QueueableAction;

    /**
     * Create a new action instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Prepare the action for execution, leveraging constructor injection.
    }

    /**
     * Execute the action.
     *
     * @param Apartment $apartment
     * @param User $user
     * @param int $bookingPeriod
     * @param string|null $bookingStartDate
     *
     * @return void
     * @throws ApartmentRentalCertificationException
     */
    public function execute(
        Apartment $apartment,
        User $user,
        int $bookingPeriod,
        string $bookingStartDate = null
    ) {
        if (!$apartment->is_active) {
            throw new ApartmentRentalCertificationException(
                'The landlord is not accepting rentals for this apartment at the moment.'
            );
        }

        if (!$apartment->is_verified) {
            throw new ApartmentRentalCertificationException('This apartment is still under review.');
        }

        // Ensure the user has submitted the KYCs
        $systemApartmentKycs = SystemApartmentKyc::required()->count();
        $customApartmentKycs = $apartment->customApartmentKycs()->count();
        $requiredKycs = $systemApartmentKycs + $customApartmentKycs;
        if ($user->apartmentKycs()->count() < $requiredKycs) {
            throw new ApartmentRentalCertificationException(
                'You have not provided all the required KYC for this apartment.'
            );
        }

        // Check if the rental is a booking
        if ($bookingStartDate && now()->parse($bookingStartDate) > now()->addDays($bookingPeriod)) {
            throw new ApartmentRentalCertificationException("Booking period exceeded. Max is {$bookingPeriod} days.");
        }

        // Check if apartment has active rental
        if (!$bookingStartDate && $apartment->rentals()->active()->exists()) {
            throw new ApartmentRentalCertificationException('Apartment is occupied at the moment.');
        }

        // Check if apartment bookings do not conflict if this apartment is booked now.
        if (
            $bookingStartDate
            && in_array(now()->parse($bookingStartDate)->toDateString(), $apartment->unavailable_booking_dates)
        ) {
            throw new ApartmentRentalCertificationException(
                'Unfortunately, there is an existing booking for this apartment at this period.'
            );
        }
    }
}
