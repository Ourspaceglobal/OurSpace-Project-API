<?php

namespace App\Notifications\User;

use App\Models\PushNotification as ModelsPushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param ModelsPushNotification $pushNotification
     * @return void
     */
    public function __construct(public ModelsPushNotification $pushNotification)
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
        $channels = [];

        if ($this->pushNotification->send_via_mail) {
            $channels[] = 'mail';
        }

        if ($this->pushNotification->send_via_system) {
            $channels[] = 'database';
        }

        return $channels;
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
            ->subject($this->pushNotification->subject)
            ->markdown('emails.push_notification', [
                'message' => $this->pushNotification->message,
            ]);
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
            'message' => $this->pushNotification->message,
        ];
    }
}
