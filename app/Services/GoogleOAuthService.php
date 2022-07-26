<?php

namespace App\Services;

use App\Enums\MediaCollection;
use App\Exceptions\OauthTokenValidationException;
use App\Models\User;
use App\Notifications\User\WelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;

class GoogleOAuthService
{
    private const API_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * Authenticate with Google.
     *
     * @param mixed $accessToken
     * @return User
     * @throws OauthTokenValidationException
     */
    public static function authenticate($accessToken): User
    {
        $response = Http::get(self::API_URL, ['id_token' => $accessToken]);

        if ($response->failed()) {
            throw new OauthTokenValidationException();
        }

        $responseData = $response->json();

        $data = [
            'first_name' => $responseData['given_name'],
            'last_name' => $responseData['family_name'],
            'email' => $responseData['email'],
            'profile_picture' => $responseData['picture'],
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

        dispatch(
            fn () => $user->addMediaFromUrl($data['profile_picture'])
                ->toMediaCollection(MediaCollection::AVATAR)
        );

        // Generate a reset token for the user.
        $resetToken = Password::createToken($user);
        $resetLink = request()->callbackUrl . "?token={$resetToken}";
        $user->notify(new WelcomeNotification($resetLink));

        return $user;
    }
}
