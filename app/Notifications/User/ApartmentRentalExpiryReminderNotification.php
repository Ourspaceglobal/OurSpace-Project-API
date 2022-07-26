<?php

namespace App\Notifications\User;

use App\Models\ApartmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApartmentRentalExpiryReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param ApartmentRental $apartmentRental
     * @return void
     */
    public function __construct(public ApartmentRental $apartmentRental)
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
            ->subject('Reminder: Apartment Rental will expire soon!')
            ->line(
                "This is to notify you that your rental of the \"{$this->apartmentRental->apartment->name}\" "
                . 'apartment will expire soon'
                . ($this->apartmentRental->is_autorenewal_active
                    ? (' and will renew automatically ' . $this->apartmentRental->expired_at->isoFormat('MMMM Do YYYY'))
                    : '')
                . '.'
            )
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
            'message' => "Reminder: Apartment Rental for {$this->apartmentRental->apartment->name} will expire soon!"
        ];
    }
}
