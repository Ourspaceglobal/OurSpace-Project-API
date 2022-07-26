<?php

namespace App\Console\Commands;

use App\Models\ApartmentRental;
use App\Notifications\User\ApartmentRentalCheckinDateReminderNotification;
use Illuminate\Console\Command;

class ApartmentRentalCheckInReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment-rentals:checkin-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for rentals that are yet to be checked-into.';

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
        ApartmentRental::active()
            ->with([
                'apartment',
                'user',
            ])
            ->whereNull('check_in_date')
            ->chunkById(100, function ($apartmentRentals) {
                foreach ($apartmentRentals as $apartmentRental) {
                    $apartmentRental->apartment->user->notify(
                        new ApartmentRentalCheckinDateReminderNotification($apartmentRental, true)
                    );
                    $apartmentRental->user->notify(
                        new ApartmentRentalCheckinDateReminderNotification($apartmentRental)
                    );
                }
            });

        return 0;
    }
}
