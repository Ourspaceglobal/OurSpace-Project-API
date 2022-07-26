<?php

namespace App\Notifications\User;

use App\Models\ApartmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApartmentRentalCheckinDateReminderNotification extends Notification implements ShouldQueue
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
        $mailMessage = (new MailMessage())
            ->subject(
                'Reminder: '
                .  ($this->isForLandlord ? "{$this->apartmentRental->user->full_name} has" : 'You have')
                . ' not checked-in!'
            )
            ->line(
                'We noticed that '
                .  ($this->isForLandlord ? "{$this->apartmentRental->user->full_name} has" : 'you have')
                . ' not checked into your apartment: '
                . "{$this->apartmentRental->apartment->name}."
            );

        if (!$this->isForLandlord) {
            $mailMessage
                ->line('No pressure, just ensure you check-in on your dashboard when you move in.')
                ->action('Check-In Now', config('frontend.dashboard') . "rentals/{$this->apartmentRental->id}");
        }

        return $mailMessage->line('Thank you for using our application!');
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
            'message' => 'Reminder: '
                .  ($this->isForLandlord ? "{$this->apartmentRental->user->full_name} has" : 'You have')
                . ' not checked into your apartment: '
                . "{$this->apartmentRental->apartment->name}.",
        ];
    }
}
