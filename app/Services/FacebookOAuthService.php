<?php

namespace App\Services;

use App\Exceptions\OauthTokenValidationException;
use App\Models\User;
use App\Notifications\User\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;

class FacebookOAuthService
{
    private const API_URL = 'https://graph.facebook.com/v12.0/';

    /**
     * Authenticate with Google.
     *
     * @param mixed $accessToken
     * @param mixed $userId
     * @return User
     * @throws OauthTokenValidationException
     */
    public static function authenticate($accessToken, $userId): User
    {
        $response = Http::get((self::API_URL . $userId), [
            'fields' => 'id,name,email',
            'access_token' => $accessToken,
        ]);

        if ($response->failed()) {
            throw new OauthTokenValidationException();
        }

        $responseData = $response->json();

        $userNames = explode(' ', $responseData['name']);
        $data = [
            'first_name' => end($userNames),
            'last_name' => reset($userNames),
            'email' => $responseData['email'],
        ];

        // Attempt a login
        if (!$user = User::where('email', $data['email'])->first()) {
            $user = self::createAccount($data);
        }

        return $user;
    }

    /**
     * Create a user account for Google client.
     *
     * @param array $data
     * @return User
     */
    public static function createAccount(array $data): User
    {
        $user = new User();
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email = $data['email'];
        $user->email_verified_at = now();
        $user->password = Hash::make(random_bytes(4));
        $user->save();

        // Generate a reset token for the user.
        $resetToken = Password::createToken($user);
        $resetLink = request()->callbackUrl . "?token={$resetToken}";
        $user->notify(new WelcomeNotification($resetLink));

        return $user;
    }
}
