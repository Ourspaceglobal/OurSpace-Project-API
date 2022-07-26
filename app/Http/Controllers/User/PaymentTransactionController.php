<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\QueryBuilder\AllowedFilter;
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
        $user = $request->user();

        $paymentTransactions = QueryBuilder::for($user->paymentTransactions())
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('payment_purpose'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('reference'),
                AllowedFilter::callback('date_added', function ($query, $date) {
                    $formattedDate = explode('/', $date);

                    throw_if(
                        count($formattedDate) <> 2,
                        \App\Exceptions\InvalidFormatException::class,
                        'Incorrect format for date filter. Expects month/year.'
                    );

                    $month = head($formattedDate);
                    $year = last($formattedDate);

                    $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
                }),
            ])
            ->with([
                'model',
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
     * @param Request $request
     * @param mixed $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(Request $request, $paymentTransaction)
    {
        $user = $request->user();

        $paymentTransaction = PaymentTransaction::query()
            ->where(fn ($query) => $query->where('id', $paymentTransaction)->orWhere('reference', $paymentTransaction))
            ->whereMorphedTo('user', $user)
            ->with('model')
            ->firstOrFail();

        return ResponseBuilder::asSuccess()
            ->withMessage('Payment transaction fetched successfully.')
            ->withData([
                'payment_transaction' => $paymentTransaction,
            ])
            ->build();
    }
}
