<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class TwofaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public const EXPIRYTIME = \App\Models\TemporaryLogin::EXPIRATION_TIME_IN_MINUTES;

    /**
     * Create a new notification instance.
     *
     * @param string $code
     * @return void
     */
    public function __construct(public string $code)
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
        $expiry = static::EXPIRYTIME;

        return (new MailMessage())
            ->subject('Secure 2FA Code')
            ->line('You requested for a secure code.')
            ->line(new HtmlString("<b>{$this->code}</b>"))
            ->line(new HtmlString(
                "<small><i>Code will expire in {$expiry} minutes.</i></small>"
            ))
            ->line(Lang::get('If this is unexpected, please do not share with anyone.'));
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
            'message' => 'We sent you a secure 2FA code.',
        ];
    }
}
