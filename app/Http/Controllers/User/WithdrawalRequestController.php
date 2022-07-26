<?php

namespace App\Http\Controllers\User;

use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Enums\WithdrawalRequestStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreWithdrawalRequestRequest;
use App\Models\PaymentTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class WithdrawalRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $withdrawalRequests = $request->user()->withdrawalRequests()
            ->with([
                'bankAccount',
            ])
            ->latest()
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal requests fetched successfully.')
            ->withData([
                'withdrawal_requests' => $withdrawalRequests,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreWithdrawalRequestRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreWithdrawalRequestRequest $request)
    {
        $user = $request->user();

        DB::beginTransaction();

        $withdrawalRequest = new WithdrawalRequest();
        $withdrawalRequest->user()->associate($user);
        $withdrawalRequest->bank_account_id = $request->bank_account_id;
        $withdrawalRequest->amount = $request->amount;
        $withdrawalRequest->reason = $request->reason;
        $withdrawalRequest->save();

        // Create the payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->model()->associate($withdrawalRequest);
        $paymentTransaction->payment_purpose = PaymentPurpose::WITHDRAWALREQUEST;
        $paymentTransaction->payment_gateway = PaymentGateway::WALLET;
        $paymentTransaction->amount = $withdrawalRequest->amount;
        $paymentTransaction->truthy_amount = $withdrawalRequest->amount;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('WR' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'model_id' => $withdrawalRequest->id,
            'model_type' => $withdrawalRequest->getMorphClass(),
        ];
        $paymentTransaction->save();

        // Link the payment transaction.
        $withdrawalRequest->payment_transaction_id = $paymentTransaction->id;
        $withdrawalRequest->saveQuietly();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Withdrawal request submitted successfully.')
            ->withData([
                'withdrawal_request' => $withdrawalRequest->withoutRelations(),
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        $this->authorize('view', $withdrawalRequest);

        $withdrawalRequest->proof_of_payment = $withdrawalRequest->proofOfPayment();
        $withdrawalRequest->load([
            'bankAccount',
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal request fetched successfully.')
            ->withData([
                'withdrawal_request' => $withdrawalRequest,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function close(WithdrawalRequest $withdrawalRequest)
    {
        $this->authorize('update', $withdrawalRequest);

        abort_if(
            $withdrawalRequest->status !== WithdrawalRequestStatuses::PENDING,
            403,
            "This request is already {$withdrawalRequest->status}"
        );

        DB::beginTransaction();

        $withdrawalRequest->paymentTransaction()->update([
            'status' => PaymentStatus::CANCEL,
        ]);
        $withdrawalRequest->status = WithdrawalRequestStatuses::CLOSED;
        $withdrawalRequest->save();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal request closed successfully.')
            ->withData([
                'withdrawal_request' => $withdrawalRequest->withoutRelations(),
            ])
            ->build();
    }
}
