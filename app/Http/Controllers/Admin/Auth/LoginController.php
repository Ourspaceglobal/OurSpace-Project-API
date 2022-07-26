<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\LoginRequest;
use App\Models\Admin;
use App\Models\TemporaryLogin;
use App\Notifications\Admin\TwofaNotification;
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
        $admin = Admin::where('email', $request->email)->first();

        if (! $admin || ! Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $apiMessage = trans('auth.once_auth_success');

        // Check for 2FA
        if ($admin->is_2fa_active) {
            $this->createTwofaCode($admin, $request->ip());
            $apiMessage = trans('auth.success_temporary');
        }

        $token = $admin->createToken('admin', [$admin->is_2fa_active ? 'requires_2fa' : '*'])->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withMessage($apiMessage)
            ->withData([
                'admin' => $admin,
                'token' => $token,
            ])
            ->build();
    }

    /**
     * Log admin out from current device.
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
     * Log out admin from all other devices.
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
     * Create temporary login for admin.
     *
     * @param Admin $admin
     * @param string $ip_address
     * @return void
     */
    public function createTwofaCode(Admin $admin, string $ip_address)
    {
        DB::beginTransaction();

        // Delete any existing code for admins.
        $admin->temporaryLogin()->delete();

        $temporaryLogin = new TemporaryLogin();
        $temporaryLogin->user()->associate($admin);
        $temporaryLogin->ip_address = $ip_address;
        $temporaryLogin->save();

        $admin->notifyNow(new TwofaNotification($temporaryLogin->code));

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
        $admin = $request->user();

        DB::beginTransaction();

        $temporaryLogin = TemporaryLogin::where('code', $request->code)
            ->where('ip_address', $request->ip())
            ->ofAdmin($admin)
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
        $admin->currentAccessToken()->delete();

        $token = $admin->createToken('admin')->plainTextToken;

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Login with 2FA was successful.')
            ->withData([
                'admin' => $admin,
                'token' => $token,
            ])
            ->build();
    }
}
