<?php

namespace App\Http\Controllers\User;

use App\Enums\MediaCollection;
use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Enums\WalletFundingStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreWalletFundingRequestRequest;
use App\Models\PaymentTransaction;
use App\Models\WalletFundingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class WalletFundingRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $walletFundingRequests = $request->user()
            ->walletFundingRequests()
            ->latest('updated_at')
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Wallet funding requests fetched successfully.')
            ->withData([
                'wallet_funding_requests' => $walletFundingRequests,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWalletFundingRequestRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreWalletFundingRequestRequest $request)
    {
        $user = $request->user();

        DB::beginTransaction();

        $walletFundingRequest = new WalletFundingRequest();
        $walletFundingRequest->user()->associate($user);
        $walletFundingRequest->amount = $request->amount;
        $walletFundingRequest->save();

        $walletFundingRequest->addMultipleMediaFromRequest(['proof_of_payment'])
            ->each(function ($fileAdder) {
                $fileAdder->toMediaCollection(MediaCollection::PROOFOFPAYMENT);
            });

        // Create the payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->model()->associate($walletFundingRequest);
        $paymentTransaction->payment_purpose = PaymentPurpose::WALLETFUNDINGREQUEST;
        $paymentTransaction->payment_gateway = PaymentGateway::WALLET;
        $paymentTransaction->amount = $walletFundingRequest->amount;
        $paymentTransaction->truthy_amount = $walletFundingRequest->amount;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('WFR' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'model_id' => $walletFundingRequest->id,
            'model_type' => $walletFundingRequest->getMorphClass(),
        ];
        $paymentTransaction->save();

        // Link the payment transaction.
        $walletFundingRequest->payment_transaction_id = $paymentTransaction->id;
        $walletFundingRequest->saveQuietly();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Wallet funding request submitted successfully.')
            ->withData([
                'wallet_funding_request' => $walletFundingRequest->withoutRelations(),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(WalletFundingRequest $walletFundingRequest)
    {
        $this->authorize('view', $walletFundingRequest);

        $walletFundingRequest->proof_of_payment = $walletFundingRequest->proofOfPayment();

        return ResponseBuilder::asSuccess()
            ->withMessage('Wallet funding request fetched successfully.')
            ->withData([
                'wallet_funding_request' => $walletFundingRequest,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function close(WalletFundingRequest $walletFundingRequest)
    {
        $this->authorize('update', $walletFundingRequest);

        abort_if(
            $walletFundingRequest->status !== WalletFundingStatuses::PENDING,
            403,
            "This request is already {$walletFundingRequest->status}"
        );

        DB::beginTransaction();

        $walletFundingRequest->paymentTransaction()->update([
            'status' => PaymentStatus::CANCEL,
        ]);
        $walletFundingRequest->status = WalletFundingStatuses::CLOSED;
        $walletFundingRequest->save();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal request closed successfully.')
            ->withData([
                'wallet_funding_request' => $walletFundingRequest->withoutRelations(),
            ])
            ->build();
    }
}
