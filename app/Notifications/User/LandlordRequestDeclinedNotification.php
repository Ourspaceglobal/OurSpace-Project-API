<?php

namespace App\Notifications\User;

use App\Models\LandlordRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class LandlordRequestDeclinedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param LandlordRequest $landlordRequest
     * @return void
     */
    public function __construct(public LandlordRequest $landlordRequest)
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
            ->subject('Request to be a landlord was declined')
            ->line('Unfortunately, your request to be a landlord was declined.')
            ->line(new HtmlString(
                '<h3>Why?</h3>'
                . "<i>{$this->landlordRequest->declination_reason}</i>"
            ))
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
            'status' => false,
            'message' => 'Your request to become a landlord was declined.'
                . "Why? {$this->landlordRequest->declination_reason}",
        ];
    }
}
