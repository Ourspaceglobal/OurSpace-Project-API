<?php

namespace App\Notifications\Admin;

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
        $mailMessage = (new MailMessage())
            ->subject('Wallet Funding Request Submitted')
            ->line("{$this->walletFundingRequest->user->full_name} has requested for "
            . "₦{$this->walletFundingRequest->amount} to be added to their wallet.");

        $this->walletFundingRequest->proofOfPayment()
            ->each(function ($media) use ($mailMessage) {
                $mailMessage->attachData($media->original_url, $media->file_name, [
                    'as' => $media->file_name,
                    'mime' => $media->mime_type,
                ]);
            });

        return $mailMessage;
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
            'message' => "{$this->walletFundingRequest->user->full_name} has requested for "
            . "₦{$this->walletFundingRequest->amount} to be added to their wallet.",
        ];
    }
}
