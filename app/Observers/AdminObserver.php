<?php

namespace App\Observers;

use App\Events\Admin\Registered;
use App\Models\Admin;
use Illuminate\Support\Facades\Password;

class AdminObserver
{
    /**
     * Handle the Admin "creating" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function creating(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "created" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function created(Admin $admin)
    {
        $token = Password::broker('admins')->createToken($admin);
        $resetLink = request('callbackUrl', config('frontend.admin.url')) . "?email={$admin->email}&token={$token}";
        event(new Registered($admin, $resetLink));
    }

    /**
     * Handle the Admin "updating" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function updating(Admin $admin)
    {
        if ($admin->isDirty(['email'])) {
            $admin->email_verified_at = null;
        }
    }

    /**
     * Handle the Admin "updated" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function updated(Admin $admin)
    {
        if ($admin->wasChanged(['email'])) {
            $admin->sendEmailVerificationNotification();
        }

        if ($admin->wasChanged(['password'])) {
            $admin->sendPasswordUpdatedNotification();
        }
    }

    /**
     * Handle the Admin "deleting" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function deleting(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "deleted" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function deleted(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "restored" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function restored(Admin $admin)
    {
        //
    }

    /**
     * Handle the Admin "force deleted" event.
     *
     * @param \App\Models\Admin $admin
     * @return void
     */
    public function forceDeleted(Admin $admin)
    {
        //
    }
}
