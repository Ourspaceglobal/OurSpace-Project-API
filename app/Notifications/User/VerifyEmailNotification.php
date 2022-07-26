<?php

namespace App\Notifications\User;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $callbackUrl
     * @param string $verificationCode
     * @return void
     */
    public function __construct(public string $callbackUrl, public string $verificationCode)
    {
        $this->onQueue('notifications');
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        return (new MailMessage())
            ->subject(Lang::get('Verify Email Address'))
            ->line(Lang::get('Please click the button below to verify your email address.'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->line(new HtmlString("You may also use this Verification Code: <b>{$this->verificationCode}</b>"))
            ->line(Lang::get('If you did not create an account, no further action is required.'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        $signedRoute = URL::temporarySignedRoute(
            'user.verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        // Pull down the signed route for restructuring with the callbackUrl
        $parsedUrl = parse_url($signedRoute);
        parse_str($parsedUrl['query'], $urlQueries);

        // Build the query parameters
        $parameters = http_build_query([
            'expires' => $urlQueries['expires'],
            'hash' => $urlQueries['hash'],
            'id' => $urlQueries['id'],
            'signature' => $urlQueries['signature']
        ]);

        return "{$this->callbackUrl}?{$parameters}";
    }
}
