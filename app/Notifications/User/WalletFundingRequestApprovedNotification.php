<?php

namespace App\Notifications\User;

use App\Models\WalletFundingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletFundingRequestApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param WalletFundingRequest $walletFundingRequest
     * @return void
     */
    public function __construct(public WalletFundingRequest $walletFundingRequest)
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
            ->subject('Wallet Funding Request Approved')
            ->line("Your request was approved and ₦{$this->walletFundingRequest->amount} was added to your wallet.")
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
            'message' => "Your request was approved and ₦{$this->walletFundingRequest->amount}"
            . 'was added to your wallet.',
        ];
    }
}
