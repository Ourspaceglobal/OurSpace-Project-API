<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Models\TemporaryLogin;
use App\Models\User;
use App\Notifications\User\TwofaNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class LoginController extends Controller
{
    /**
     * Login existing users to the application.
     *
     * @param LoginRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $apiMessage = trans('auth.once_auth_success');

        // Check for 2FA
        if ($user->is_2fa_active) {
            $this->createTwofaCode($user, $request->ip());
            $apiMessage = trans('auth.success_temporary');
        }

        $token = $user->createToken('user', [$user->is_2fa_active ? 'requires_2fa' : '*'])->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withMessage($apiMessage)
            ->withData([
                'user' => $user,
                'token' => $token,
            ])
            ->build();
    }

    /**
     * Log user out from current device.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ResponseBuilder::asSuccess()
            ->withMessage('Logout was successful.')
            ->build();
    }

    /**
     * Log out user from all other devices.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logoutOtherDevices(Request $request)
    {
        $user = $request->user();

        $tokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $tokenId)->delete();

        return ResponseBuilder::asSuccess()
            ->withMessage('All other devices have been logged successfully.')
            ->build();
    }

    /**
     * Create temporary login for user.
     *
     * @param User $user
     * @param string $ip_address
     * @return void
     */
    public function createTwofaCode(User $user, string $ip_address)
    {
        DB::beginTransaction();

        // Delete any existing code for users.
        $user->temporaryLogin()->delete();

        $temporaryLogin = new TemporaryLogin();
        $temporaryLogin->user()->associate($user);
        $temporaryLogin->ip_address = $ip_address;
        $temporaryLogin->save();

        $user->notifyNow(new TwofaNotification($temporaryLogin->code));

        DB::commit();
    }

    /**
     * Fetch new second factor code.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getNewSecondFactorCode(Request $request)
    {
        $this->createTwofaCode($request->user(), $request->ip());

        return ResponseBuilder::asSuccess()
            ->withMessage('New second factor authentication code sent')
            ->build();
    }

    /**
     * Validate user login with 2FA.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function validateTwofaCode(Request $request)
    {
        $user = $request->user();

        DB::beginTransaction();

        $temporaryLogin = TemporaryLogin::where('code', $request->code)
            ->where('ip_address', $request->ip())
            ->ofuser($user)
            ->first();

        if (!$temporaryLogin) {
            return ResponseBuilder::asError(100)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage(trans('auth.code.invalid'))
                ->withData(['code' => [trans('auth.code.invalid')]])
                ->build();
        }

        if ($temporaryLogin->isExpired()) {
            return ResponseBuilder::asError(100)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage(trans('auth.code.expired'))
                ->withData(['code' => [trans('auth.code.expired')]])
                ->build();
        }

        $temporaryLogin->delete();
        $user->currentAccessToken()->delete();

        $token = $user->createToken('user')->plainTextToken;

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Login with 2FA was successful.')
            ->withData([
                'user' => $user,
                'token' => $token,
            ])
            ->build();
    }
}
