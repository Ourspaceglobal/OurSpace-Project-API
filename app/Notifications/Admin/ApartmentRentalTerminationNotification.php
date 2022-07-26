<?php

namespace App\Notifications\Admin;

use App\Models\ApartmentRental;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ApartmentRentalTerminationNotification extends Notification implements ShouldQueue
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
            ->line(
                "{$this->apartmentRental->user->full_name}'s rental of the {$this->apartmentRental->apartment->name} "
                . "apartment was terminated."
            )
            ->line(new HtmlString("<b>Reason:</b> <i>{$this->apartmentRental->termination_reason}</i>"));
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
            'message' => "{$this->apartmentRental->user->full_name}'s rental of the "
                . $this->apartmentRental->apartment->name
                . " apartment was terminated. Why? {$this->apartmentRental->termination_reason}"
        ];
    }
}
