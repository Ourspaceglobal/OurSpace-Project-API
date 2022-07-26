<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\BasicUserInfoExtract;
use App\Enums\PaymentStatus;
use App\Enums\WalletFundingStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeclineWalletFundingRequestRequest;
use App\Models\WalletFundingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

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
        $walletFundingRequests = QueryBuilder::for(WalletFundingRequest::class)
            ->allowedFilters([
                'user_id',
                'status',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'amount',
                'status',
                'updated_at',
                'created_at',
            ])
            ->allowedIncludes([
                AllowedInclude::custom('user', new BasicUserInfoExtract([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'wallet_balance',
                ])),
            ])
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
     * Display the specified resource.
     *
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(WalletFundingRequest $walletFundingRequest)
    {
        $walletFundingRequest->proof_of_payment = $walletFundingRequest->proofOfPayment();

        $walletFundingRequest = $walletFundingRequest->load([
            'user' => fn($query) => $query->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'wallet_balance',
            ]),
        ]);

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
    public function approve(WalletFundingRequest $walletFundingRequest)
    {
        abort_if(
            $walletFundingRequest->status !== WalletFundingStatuses::PENDING,
            403,
            "This request is already {$walletFundingRequest->status}"
        );

        DB::beginTransaction();

        $user = $walletFundingRequest->user;

        // Deduct amount from user's wallet
        $user->wallet_balance = $user->wallet_balance + $walletFundingRequest->amount;
        $user->save();

        $walletFundingRequest->paymentTransaction()->update([
            'status' => PaymentStatus::SUCCESS,
        ]);
        $walletFundingRequest->status = WalletFundingStatuses::APPROVED;
        $walletFundingRequest->save();

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Wallet funding request approved successfully.')
            ->withData([
                'wallet_funding_request' => $walletFundingRequest->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Decline the specified resource from storage.
     *
     * @param DeclineWalletFundingRequestRequest $request
     * @param \App\Models\WalletFundingRequest $walletFundingRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function decline(DeclineWalletFundingRequestRequest $request, WalletFundingRequest $walletFundingRequest)
    {
        abort_if(
            $walletFundingRequest->status !== WalletFundingStatuses::PENDING,
            403,
            "This request is already {$walletFundingRequest->status}"
        );

        $walletFundingRequest->is_declined = true;
        $walletFundingRequest->declination_reason = $request->reason;
        $walletFundingRequest->status = WalletFundingStatuses::DECLINED;
        $walletFundingRequest->declined_at = now();
        $walletFundingRequest->save();

        $walletFundingRequest->paymentTransaction()->update([
            'narration' => substr($walletFundingRequest->declination_reason, 0, 191),
            'status' => PaymentStatus::REJECT,
        ]);

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Wallet funding request declined successfully.')
            ->withData([
                'wallet_funding_request' => $walletFundingRequest->unsetRelation('user'),
            ])
            ->build();
    }
}
