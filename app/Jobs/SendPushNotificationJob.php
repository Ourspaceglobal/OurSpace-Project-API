<?php

namespace App\Jobs;

use App\Models\PushNotification;
use App\Models\User;
use App\Notifications\User\PushNotification as UserPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param PushNotification $pushNotification
     * @return void
     */
    public function __construct(public PushNotification $pushNotification)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pushNotification = $this->pushNotification;

        if ($pushNotification->trashed()) {
            return;
        }

        $users = $pushNotification->users();
        if ($users === 'ALL') {
            $users = User::select('id')->all();
        }

        DB::beginTransaction();

        Notification::send($users, new UserPushNotification($pushNotification));

        $pushNotification->is_sent = true;
        $pushNotification->save();

        DB::commit();
    }
}
