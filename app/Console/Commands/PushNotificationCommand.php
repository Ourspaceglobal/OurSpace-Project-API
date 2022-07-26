<?php

namespace App\Console\Commands;

use App\Jobs\SendPushNotificationJob;
use App\Models\PushNotification;
use Illuminate\Console\Command;

class PushNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send-out push notifications';

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
        PushNotification::unsent()
            ->whereBetween('send_at', [
                now(),
                now()->addMinutes(30)
            ])
            ->chunkById(10, function ($pushNotifications) {
                foreach ($pushNotifications as $pushNotification) {
                    SendPushNotificationJob::dispatch($pushNotification)->delay($pushNotification->send_at);
                }
            });

        return 0;
    }
}
