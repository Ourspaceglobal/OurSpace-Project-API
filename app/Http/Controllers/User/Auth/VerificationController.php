<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class VerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param EmailVerificationRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function verify(EmailVerificationRequest $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return ResponseBuilder::asError(100)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage('User email has previously being verified')
                ->build();
        }

        $request->fulfill();

        return ResponseBuilder::asSuccess()
            ->withMessage('User email verified successfully!')
            ->build();
    }

    /**
     * Resend the email verification notification.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resend(Request $request)
    {
        $request->validate([
            'callbackUrl' => 'required|url',
        ]);

        if ($request->user()->hasVerifiedEmail()) {
            return ResponseBuilder::asError(100)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage('User already has a verified email')
                ->build();
        }

        $request->user()->sendEmailVerificationNotification();

        return ResponseBuilder::asSuccess()
            ->withMessage('We have sent you another email verification link')
            ->build();
    }
}
