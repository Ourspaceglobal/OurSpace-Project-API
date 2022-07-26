<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\QueryBuilder;

class PaymentTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $paymentTransactions = QueryBuilder::for(PaymentTransaction::class)
            ->allowedFilters([
                'payment_purpose',
                'status',
                'reference',
            ])
            ->defaultSort('-updated_at')
            ->allowedSorts([
                'status',
                'created_at',
                'updated_at',
            ])
            ->paginate($request->per_page)
            ->withQueryString();

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment transactions fetched successfully.')
            ->withData([
                'payment_transactions' => $paymentTransactions,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(PaymentTransaction $paymentTransaction)
    {
        $paymentTransaction->load([
            'user' => fn ($query) => $query->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone_number',
            ]),
        ]);

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment transaction fetched successfully.')
            ->withData([
                'payment_transaction' => $paymentTransaction,
            ])
            ->build();
    }
}
