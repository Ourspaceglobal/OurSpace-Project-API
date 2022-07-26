<?php

namespace App\Console\Commands;

use App\Models\ApartmentRental;
use App\Notifications\User\ApartmentRentalExpiryReminderNotification;
use Illuminate\Console\Command;

class ApartmentRentalReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment-rentals:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for almost expired rentals.';

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
            ->whereDate('expired_at', now()->subDays(7))
            ->chunkById(100, function ($apartmentRentals) {
                foreach ($apartmentRentals as $apartmentRental) {
                    // Discard reminders for rentals that already have another active rental in the future
                    $nextRent = ApartmentRental::active()->whereBelongsTo($apartmentRental->apartment)
                        ->whereBelongsTo($apartmentRental->user)
                        ->where('expired_at', '>', $apartmentRental->expired_at)
                        ->exists();

                    if (!$nextRent) {
                        $apartmentRental->user->notify(new ApartmentRentalExpiryReminderNotification($apartmentRental));
                    }
                }
            });

        return 0;
    }
}
