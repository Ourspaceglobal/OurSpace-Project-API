<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletBalanceUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $measure;
    public float $difference;

    /**
     * Create a new notification instance.
     *
     * @param float $oldBalance
     * @param float $currentBalance
     * @return void
     */
    public function __construct(public float $oldBalance, public float $currentBalance)
    {
        $this->onQueue('notifications');

        $this->measure = $currentBalance > $this->oldBalance ? 'added to' : 'deducted from';
        $this->difference = abs($oldBalance - $currentBalance);
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
            ->subject('Wallet Balance Update')
            ->line("₦{$this->difference} was {$this->measure} your wallet.");
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
            'message' => "₦{$this->difference} was {$this->measure} from your wallet.",
        ];
    }
}
