<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ForgotPasswordRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param ForgotPasswordRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($status)
            : $this->sendResetLinkFailedResponse($status);
    }

    /**
     * Get the response for a successful sent password reset link.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResetLinkResponse($response)
    {
        return ResponseBuilder::asSuccess()
            ->withMessage(trans($response))
            ->build();
    }

    /**
     * Get the response for a failed send password reset.
     *
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResetLinkFailedResponse($response)
    {
        return ResponseBuilder::asError(100)
            ->withHttpCode(Response::HTTP_BAD_REQUEST)
            ->withMessage(trans($response))
            ->build();
    }
}
