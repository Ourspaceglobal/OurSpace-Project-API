<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\QueryBuilder\BasicUserInfoExtract;
use App\Enums\MediaCollection;
use App\Enums\PaymentStatus;
use App\Enums\WithdrawalRequestStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveWithdrawalRequestRequest;
use App\Http\Requests\Admin\DeclineWithdrawalRequestRequest;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

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
        $withdrawalRequests = QueryBuilder::for(WithdrawalRequest::class)
            ->allowedFilters([
                'user_id',
                'status',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'amount',
                'reason',
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
            ->withMessage('Withdrawal requests fetched successfully.')
            ->withData([
                'withdrawal_requests' => $withdrawalRequests,
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
        $withdrawalRequest->proof_of_payment = $withdrawalRequest->proofOfPayment();
        $withdrawalRequest = $withdrawalRequest->load([
            'user' => fn($query) => $query->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'wallet_balance',
            ]),
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
     * @param ApproveWithdrawalRequestRequest $request
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function approve(ApproveWithdrawalRequestRequest $request, WithdrawalRequest $withdrawalRequest)
    {
        abort_if(
            $withdrawalRequest->status !== WithdrawalRequestStatuses::PENDING,
            403,
            "This request is already {$withdrawalRequest->status}"
        );

        DB::beginTransaction();

        // Double-check the wallet balance before approval
        $user = $withdrawalRequest->user;
        if (($user->wallet_balance - $withdrawalRequest->amount) < 0) {
            return $this->declineRequest($withdrawalRequest, 'Insufficient funds in wallet.');
        }

        // Deduct amount from user's wallet
        $user->wallet_balance = $user->wallet_balance - $withdrawalRequest->amount;
        $user->save();

        $withdrawalRequest->paymentTransaction()->update([
            'status' => PaymentStatus::SUCCESS,
        ]);

        $withdrawalRequest->status = WithdrawalRequestStatuses::APPROVED;
        $withdrawalRequest->save();

        if ($request->proof_of_payment) {
            $withdrawalRequest->addMultipleMediaFromRequest(['proof_of_payment'])
                ->each(function ($fileAdder) {
                    $fileAdder->toMediaCollection(MediaCollection::PROOFOFPAYMENT);
                });
        }

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal request approved successfully.')
            ->withData([
                'withdrawal_request' => $withdrawalRequest->unsetRelation('user'),
            ])
            ->build();
    }

    /**
     * Decline the specified resource from storage.
     *
     * @param DeclineWithdrawalRequestRequest $request
     * @param \App\Models\WithdrawalRequest $withdrawalRequest
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function decline(DeclineWithdrawalRequestRequest $request, WithdrawalRequest $withdrawalRequest)
    {
        abort_if(
            $withdrawalRequest->status !== WithdrawalRequestStatuses::PENDING,
            403,
            "This request is already {$withdrawalRequest->status}"
        );

        return $this->declineRequest($withdrawalRequest, $request->reason);
    }

    /**
     * Decline a withdrawal request.
     *
     * @param WithdrawalRequest $withdrawalRequest
     * @param string $reason
     * @return void
     */
    protected function declineRequest(WithdrawalRequest $withdrawalRequest, string $reason)
    {
        $withdrawalRequest->is_declined = true;
        $withdrawalRequest->declination_reason = $reason;
        $withdrawalRequest->status = WithdrawalRequestStatuses::DECLINED;
        $withdrawalRequest->declined_at = now();
        $withdrawalRequest->save();

        $withdrawalRequest->paymentTransaction()->update([
            'narration' => substr($withdrawalRequest->declination_reason, 0, 191),
            'status' => PaymentStatus::REJECT,
        ]);

        DB::commit();

        return ResponseBuilder::asSuccess()
            ->withMessage('Withdrawal request declined successfully.')
            ->withData([
                'withdrawal_request' => $withdrawalRequest->unsetRelation('user'),
            ])
            ->build();
    }
}
