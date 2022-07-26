<?php

namespace App\Notifications\User;

use App\Models\Apartment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApartmentPriceUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param Apartment $apartment
     * @return void
     */
    public function __construct(public Apartment $apartment)
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
            ->subject('Update: Apartment Price Updated!')
            ->greeting("Dear {$notifiable->full_name},")
            ->line("This is to notify you that the {$this->apartment->name} apartment's price was updated.")
            ->line("The new price is ₦" . number_format($this->apartment->price, 2)
                . " to run per {$this->apartment->apartmentDuration->period}")
            ->line('Please note that this change does not affect your current rent, '
                . 'it will only take effect for future payments.')
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
            'message' => "This is to notify you that the {$this->apartment->name} apartment's price was updated.",
        ];
    }
}
