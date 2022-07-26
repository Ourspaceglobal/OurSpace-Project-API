<?php

namespace App\Console\Commands;

use App\Models\ApartmentRental;
use App\Services\PaystackService;
use App\Services\User\ApartmentRentalService;
use Illuminate\Console\Command;

class ApartmentRentalRenewalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apartment-rentals:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew expired apartment rentals.';

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
     * @param PaystackService $paystackService
     * @return int
     */
    public function handle(PaystackService $paystackService)
    {
        ApartmentRental::active()
            ->where('is_autorenewal_active', true)
            ->whereBetween('expired_at', [
                now(),
                now()->addMinutes(30)
            ])
            ->chunkById(100, function ($apartmentRentals) use ($paystackService) {
                foreach ($apartmentRentals as $apartmentRental) {
                    // Discard reminders for rentals that already have another active rental in the future
                    $nextRent = ApartmentRental::active()->whereBelongsTo($apartmentRental->apartment)
                        ->whereBelongsTo($apartmentRental->user)
                        ->where('expired_at', '>', $apartmentRental->expired_at)
                        ->exists();

                    if (!$nextRent) {
                        ApartmentRentalService::renew($apartmentRental, $paystackService);
                    }
                }
            });

        return 0;
    }
}
