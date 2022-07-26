<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\SocialAuthRequest;
use App\Services\FacebookOAuthService;
use App\Services\GoogleOAuthService;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class SocialAuthController extends Controller
{
    /**
     * Authenticate the user via social account.
     *
     * @param SocialAuthRequest $request
     * @return void
     */
    public function index(SocialAuthRequest $request)
    {
        $accessToken = $request->access_token;

        $user = match ($request->socialApp) {
            'google' => GoogleOAuthService::authenticate($accessToken),
            'facebook' => FacebookOAuthService::authenticate($accessToken, $request->userId),
        };

        $token = $user->createToken('user')->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withMessage('Login was successful.')
            ->withData([
                'user' => $user,
                'token' => $token,
            ])
            ->build();
    }
}
