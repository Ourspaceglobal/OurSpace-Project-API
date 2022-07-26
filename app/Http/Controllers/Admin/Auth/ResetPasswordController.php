<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Events\Admin\ConfirmResetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given admin's password.
     *
     * @param ResetPasswordRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function reset(ResetPasswordRequest $request)
    {
        $callbackUrl = $request->callbackUrl;

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) use ($callbackUrl) {
                $admin->forceFill([
                    'password' => Hash::make($password),
                ]);
                $admin->save();

                event(new ConfirmResetPassword($admin, $callbackUrl));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? $this->sendResetResponse($status)
            : $this->sendResetFailedResponse($status);
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResetResponse($response)
    {
        return ResponseBuilder::asSuccess()
            ->withMessage(trans($response))
            ->build();
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResetFailedResponse($response)
    {
        return ResponseBuilder::asError(100)
            ->withHttpCode(Response::HTTP_BAD_REQUEST)
            ->withMessage(trans($response))
            ->build();
    }
}
