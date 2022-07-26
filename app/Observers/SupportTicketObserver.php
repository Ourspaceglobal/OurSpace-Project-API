<?php

namespace App\Observers;

use App\Models\Admin;
use App\Models\SupportTicket;
use App\Notifications\Admin\SupportTicketSubmittedNotification as AdminSupportTicketSubmittedNotification;
use App\Notifications\User\SupportTicketSubmittedNotification;
use Illuminate\Support\Facades\Notification;

class SupportTicketObserver
{
    /**
     * Handle the SupportTicket "creating" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function creating(SupportTicket $supportTicket)
    {
        $supportTicket->reference = strtoupper('#' . last(explode('-', $supportTicket->id)) . now()->timestamp);
    }

    /**
     * Handle the SupportTicket "created" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function created(SupportTicket $supportTicket)
    {
        $admins = Admin::permission('receive email notifications')->get();
        Notification::send($admins, new AdminSupportTicketSubmittedNotification($supportTicket));

        $supportTicket->user->notify(new SupportTicketSubmittedNotification($supportTicket));
    }

    /**
     * Handle the SupportTicket "updating" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function updating(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Handle the SupportTicket "updated" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function updated(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Handle the SupportTicket "deleting" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function deleting(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Handle the SupportTicket "deleted" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function deleted(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Handle the SupportTicket "restored" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function restored(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Handle the SupportTicket "force deleted" event.
     *
     * @param \App\Models\SupportTicket $supportTicket
     * @return void
     */
    public function forceDeleted(SupportTicket $supportTicket)
    {
        //
    }
}
