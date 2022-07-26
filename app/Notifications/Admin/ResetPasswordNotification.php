<?php

namespace App\Notifications\Admin;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends ResetPassword
{
    use Queueable;

    /**
     * Create a notification instance.
     *
     * @param string $token
     * @param string $code
     * @param string $callbackUrl
     * @return void
     */
    public function __construct(string $token, public string $code, public string $callbackUrl)
    {
        $this->onQueue('notifications');
        $this->token = $token;
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
     * Get the reset password notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(Lang::get('Reset Password Notification'))
            ->line(
                Lang::get('You are receiving this email because we received a password reset request for your account.')
            )
            ->action(Lang::get('Reset Password'), $url)
            ->line(new HtmlString("You may also use this code: <b>{$this->code}</b>"))
            ->line(Lang::get('This password reset link will expire in :count minutes.', [
                'count' => config('auth.passwords.admins.expire'),
            ]))
            ->line(Lang::get('If you did not request a password reset, no further action is required.'));
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
            'message' => 'You requested to reset your password',
        ];
    }

    /**
     * Get the reset URL for the given notifiable.
     *
     * @param mixed $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        $query = http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return "{$this->callbackUrl}?{$query}";
    }
}
