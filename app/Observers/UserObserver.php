<?php

namespace App\Observers;

use App\Enums\UserType;
use App\Models\User;
use App\Notifications\User\LandlordWelcomeNotification;
use App\Notifications\User\WalletBalanceUpdateNotification;
use Illuminate\Auth\Events\Registered;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function creating(User $user)
    {
        // update user finder
        $user->finder = strtoupper(head(explode('-', $user->id)) . now()->timestamp);
    }

    /**
     * Handle the User "created" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function created(User $user)
    {
        event(new Registered($user));
    }

    /**
     * Handle the User "updating" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function updating(User $user)
    {
        if ($user->isDirty(['email'])) {
            $user->email_verified_at = null;
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function updated(User $user)
    {
        if ($user->wasChanged(['email'])) {
            $user->sendEmailVerificationNotification();
        }

        if ($user->wasChanged(['password'])) {
            $user->sendPasswordUpdatedNotification();
        }

        if ($user->wasChanged('type') && $user->type === UserType::LANDLORD) {
            $user->notify(new LandlordWelcomeNotification());
        }

        if ($user->wasChanged('wallet_balance')) {
            $oldBalance = $user->getOriginal('wallet_balance');
            $user->notify(new WalletBalanceUpdateNotification($oldBalance, $user->wallet_balance));
        }
    }

    /**
     * Handle the User "deleting" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleting(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
