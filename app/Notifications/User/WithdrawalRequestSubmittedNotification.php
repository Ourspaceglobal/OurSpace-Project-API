<?php

namespace App\Notifications\User;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('Withdrawal Request Submitted')
            ->line("This is to acknowledge your request to withdraw ₦{$this->withdrawalRequest->amount} "
            . "from your wallet, into your bank account: {$this->withdrawalRequest->bankAccount->bank_name} - "
            . "{$this->withdrawalRequest->bankAccount->account_number}")
            ->line('If you did not make this request, kindly log onto your dashboard to close it immediately.')
            ->action('Open Dashboard', config('frontend.dashboard'));
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
            'message' => "You requested a withdrawal of ₦{$this->withdrawalRequest->amount} "
            . "into your bank account: {$this->withdrawalRequest->bankAccount->bank_name} - "
            . "{$this->withdrawalRequest->bankAccount->account_number}",
        ];
    }
}
