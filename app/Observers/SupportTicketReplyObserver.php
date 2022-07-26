<?php

namespace App\Observers;

use App\Models\SupportTicketReply;
use App\Notifications\Admin\SupportTicketUpdatedNotification as AdminSupportTicketUpdatedNotification;
use App\Notifications\User\SupportTicketUpdatedNotification;

class SupportTicketReplyObserver
{
    /**
     * Handle the SupportTicketReply "creating" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function creating(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "created" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function created(SupportTicketReply $supportTicketReply)
    {
        $supportTicket = $supportTicketReply->supportTicket;
        $supportTicket->is_open = true;
        $supportTicket->save();

        if ($supportTicket->is_open) {
            if ($supportTicketReply->user_type === 'admin') {
                $supportTicket->user->notify(new SupportTicketUpdatedNotification($supportTicket));
            } else {
                $supportTicketReplyForAdmin = $supportTicket->replies()->where('user_type', 'admin')->latest()->first();
                $supportTicketReplyForAdmin?->user->notify(new AdminSupportTicketUpdatedNotification($supportTicket));
            }
        }
    }

    /**
     * Handle the SupportTicketReply "updating" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function updating(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "updated" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function updated(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "deleting" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function deleting(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "deleted" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function deleted(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "restored" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function restored(SupportTicketReply $supportTicketReply)
    {
        //
    }

    /**
     * Handle the SupportTicketReply "force deleted" event.
     *
     * @param \App\Models\SupportTicketReply $supportTicketReply
     * @return void
     */
    public function forceDeleted(SupportTicketReply $supportTicketReply)
    {
        //
    }
}
