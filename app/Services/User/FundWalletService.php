<?php

namespace App\Services\User;

use App\Events\User\WalletHistoryRecorder;
use App\Models\PaymentTransaction;

class FundWalletService
{
    /**
     * Fund the user's wallet.
     *
     * @param PaymentTransaction $paymentTransaction
     * @return void
     */
    public static function serve(PaymentTransaction $paymentTransaction)
    {
        $user = $paymentTransaction->user;
        $user->wallet_balance += $paymentTransaction->amount;
        $user->save();

        event(new WalletHistoryRecorder(
            $user,
            $paymentTransaction,
            "You have funded your wallet with ₦{$paymentTransaction->amount}."
        ));
    }
}
