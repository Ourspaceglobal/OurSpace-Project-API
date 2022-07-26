<?php

namespace App\Notifications\Admin;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $callbackUrl
     * @param string|null $resetLink
     * @return void
     */
    public function __construct(public string $callbackUrl)
    {
        $this->onQueue('notifications');
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
            'admin.verification.verify',
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
