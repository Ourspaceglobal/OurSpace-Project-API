<?php

namespace App\Console\Commands;

use App\Models\Apartment;
use Illuminate\Console\Command;

class UpdateApartmentRentalAvailabilityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartments:rent-availability
        {apartment? : The ID of the apartment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the rent availability for all apartments.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $apartmentId = $this->argument('apartment');

        if ($apartmentId) {
            $apartment = Apartment::withTrashed()->findOrFail($apartmentId);

            $apartment->available_for_rent = $apartment->rentals()->active()->doesntExist() &&
                $apartment->bookings()->whereBetween(
                    'started_at',
                    [now(), now()->addDays($apartment->apartmentDuration()->value('duration_in_days'))]
                )->doesntExist();
            $apartment->unavailable_booking_dates = $apartment->getUnavailableBookingDates();
            $apartment->saveQuietly();
        } else {
            Apartment::withTrashed()
                ->has('rentals')
                ->chunkById(50, function ($apartments) {
                    foreach ($apartments as $apartment) {
                        $apartment->available_for_rent = $apartment->rentals()->active()->doesntExist() &&
                            $apartment->bookings()->whereBetween(
                                'started_at',
                                [now(), now()->addDays($apartment->apartmentDuration()->value('duration_in_days'))]
                            )->doesntExist();
                        $apartment->unavailable_booking_dates = $apartment->getUnavailableBookingDates();
                        $apartment->saveQuietly();
                    }
                });
        }

        return 0;
    }
}
