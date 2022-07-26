<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreBankAccountRequest;
use App\Http\Requests\User\UpdateBankAccountRequest;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class BankAccountController extends Controller
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

        $bankAccounts = BankAccount::query()
            ->whereBelongsTo($user)
            ->paginate();

        return ResponseBuilder::asSuccess()
            ->withMessage('User bank accounts fetched successfully.')
            ->withData([
                'bank_accounts' => $bankAccounts,
            ])
            ->build();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBankAccountRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(StoreBankAccountRequest $request)
    {
        $bankAccount = new BankAccount();
        $bankAccount->user_id = $request->user()->id;
        $bankAccount->account_number = $request->account_number;
        $bankAccount->account_name = $request->account_name;
        $bankAccount->bank_name = $request->bank_name;
        $bankAccount->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Bank account stored successfully.')
            ->withData([
                'bank_account' => $bankAccount,
            ])
            ->build();
    }

    /**
     * Display the specified resource.
     *
     * @param BankAccount $bankAccount
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function show(BankAccount $bankAccount)
    {
        $this->authorize('view', $bankAccount);

        return ResponseBuilder::asSuccess()
            ->withMessage('Bank account fetched successfully.')
            ->withData([
                'bank_account' => $bankAccount,
            ])
            ->build();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBankAccountRequest $request
     * @param \App\Models\BankAccount $bankAccount
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount)
    {
        $this->authorize('update', $bankAccount);

        $bankAccount->bank_name = $request->bank_name;
        $bankAccount->account_number = $request->account_number;
        $bankAccount->account_name = $request->account_name;
        $bankAccount->save();

        return ResponseBuilder::asSuccess()
            ->withMessage('Bank account updated successfully.')
            ->withData([
                'bank_account' => $bankAccount,
            ])
            ->build();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\BankAccount $bankAccount
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(BankAccount $bankAccount)
    {
        $this->authorize('delete', $bankAccount);

        $bankAccount->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
