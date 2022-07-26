<?php

namespace App\Notifications\User;

use App\Enums\MediaCollection;
use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestApprovedNotification extends Notification implements ShouldQueue
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
        $mailMessage = (new MailMessage())
            ->subject('Withdrawal Request Approved')
            ->line("Your request was approved and ₦{$this->withdrawalRequest->amount} was deducted from your wallet.")
            ->line('Thank you for using our application!');

        $this->withdrawalRequest->proofOfPayment()
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
            'message' => "Your request was approved and ₦{$this->withdrawalRequest->amount}"
            . 'was deducted from your wallet.',
        ];
    }
}
