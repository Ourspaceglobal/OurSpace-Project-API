<?php

namespace App\Notifications\User;

use App\Models\ApartmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApartmentBookingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param ApartmentRental $apartmentRental
     * @param bool $isForLandlord
     * @return void
     */
    public function __construct(public ApartmentRental $apartmentRental, public bool $isForLandlord = false)
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
            ->line(
                ($this->isForLandlord ? "{$this->apartmentRental->user->full_name} " : 'You have ')
                . "booked the apartment: {$this->apartmentRental->apartment->name} to start "
                . $this->apartmentRental->started_at->isoFormat('MMMM Do YYYY')
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
            'message' => ($this->isForLandlord ? "{$this->apartmentRental->user->full_name} " : 'You have ')
                . "booked the apartment: {$this->apartmentRental->apartment->name} to start "
                . $this->apartmentRental->started_at->isoFormat('MMMM Do YYYY'),
        ];
    }
}
