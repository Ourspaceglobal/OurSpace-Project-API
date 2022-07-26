<?php

namespace App\Notifications\User;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class WithdrawalRequestDeclinedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param WithdrawalRequest $withdrawalRequest
     * @return void
     */
    public function __construct(public WithdrawalRequest $withdrawalRequest)
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
            ->subject('Withdrawal Request Declined')
            ->line("Your request to withdraw ₦{$this->withdrawalRequest->amount} was declined.")
            ->line(new HtmlString("<i>{$this->withdrawalRequest->declination_reason}</i>"))
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
            'message' => "Your request to withdraw ₦{$this->withdrawalRequest->amount} was declined."
            . "'{$this->withdrawalRequest->declination_reason}'"
        ];
    }
}
