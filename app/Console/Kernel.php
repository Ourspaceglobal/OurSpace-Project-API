<?php

namespace App\Console;

use App\Console\Commands\ApartmentRentalCheckInReminderCommand;
use App\Console\Commands\ApartmentRentalReminderCommand;
use App\Console\Commands\ApartmentRentalRenewalCommand;
use App\Console\Commands\PushNotificationCommand;
use App\Console\Commands\UpdateApartmentRentalAvailabilityCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(PushNotificationCommand::class)->everyThirtyMinutes();

        $schedule->command(ApartmentRentalReminderCommand::class)->daily();

        $schedule->command(ApartmentRentalRenewalCommand::class)->everyThirtyMinutes();

        $schedule->command(UpdateApartmentRentalAvailabilityCommand::class)->daily();

        $schedule->command(ApartmentRentalCheckInReminderCommand::class)->cron('0 0 */3 * *');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
