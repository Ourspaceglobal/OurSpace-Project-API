<?php

namespace App\Notifications\User;

use App\Models\WalletFundingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletFundingRequestSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('Wallet Funding Request Submitted')
            ->line("This is to acknowledge your request to fund your wallet "
            . "with ₦{$this->walletFundingRequest->amount}")
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
            'message' => "You requested to fund your wallet with ₦{$this->walletFundingRequest->amount}",
        ];
    }
}
