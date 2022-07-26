<?php

namespace App\Notifications\Admin;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class SupportTicketSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param SupportTicket $supportTicket
     * @return void
     */
    public function __construct(public SupportTicket $supportTicket)
    {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->line("{$this->supportTicket->user->full_name} submitted a support ticket: "
            . "{$this->supportTicket->reference}")
            ->line(new HtmlString("<h5>{$this->supportTicket->subject}</h5>"
            . "<p>{$this->supportTicket->message}</p>"))
            ->action('Review Ticket', config('frontend.admin.url'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'status' => true,
            'message' => "{$this->supportTicket->user->full_name} submitted a suppor ticket: "
            . "{$this->supportTicket->reference}",
        ];
    }
}
