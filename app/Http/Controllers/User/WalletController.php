<?php

namespace App\Http\Controllers\User;

use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddFundsToWalletWithPaystackRequest;
use App\Models\PaymentCard;
use App\Models\PaymentTransaction;
use App\Services\PaystackService;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class WalletController extends Controller
{
    /**
     * Add funds to user's wallet using Paystack.
     *
     * @param AddFundsToWalletWithPaystackRequest $request
     * @param PaystackService $paystackService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addFundsWithPaystack(AddFundsToWalletWithPaystackRequest $request, PaystackService $paystackService)
    {
        $user = $request->user();

        // Create the payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->payment_purpose = PaymentPurpose::WALLETFUND;
        $paymentTransaction->payment_gateway = PaymentGateway::PAYSTACK;
        $paymentTransaction->amount = $request->amount;
        $paymentTransaction->truthy_amount = $request->amount;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('WF' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
        ];
        $paymentTransaction->save();

        // Create the paystack transaction with transaction->reference
        $data = [];
        $message = 'Request was successful.';

        if ($paymentCard = PaymentCard::find($request->payment_card_id)) {
            $transaction = $paystackService->getFactory()->transaction->charge([
                'reference' => $paymentTransaction->reference,
                'amount' => (int) ($paymentTransaction->amount * 100),
                'currency' => $paymentTransaction->currency,
                'email' => $paymentTransaction->user->email,
                'authorization_code' => $paymentCard->authorization_code,
            ]);

            if ($transaction->status) {
                $data = [
                    'status' => $transaction->data->status,
                    'reference' => $transaction->data->reference,
                ];
                $message = $transaction->data->gateway_response;
            } else {
                $message = $transaction->message;
            }
        } else {
            $transaction = $paystackService->getFactory()->transaction->initialize([
                'reference' => $paymentTransaction->reference,
                'amount' => (int) ($paymentTransaction->amount * 100),
                'currency' => $paymentTransaction->currency,
                'email' => $paymentTransaction->user->email,
                'callback_url' => $request->callbackUrl,
            ]);

            $data = [
                'authorization_url' => $transaction->data->authorization_url,
                'access_code' => $transaction->data->access_code,
                'reference' => $transaction->data->reference,
            ];

            $message = 'Payment intent generated successfully.';
        }

        return ResponseBuilder::asSuccess()
            ->withMessage($message)
            ->withData($data)
            ->build();
    }
}
