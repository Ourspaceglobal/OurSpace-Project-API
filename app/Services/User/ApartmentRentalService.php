<?php

namespace App\Services\User;

use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Events\User\TempWalletModifier;
use App\Events\User\WalletHistoryRecorder;
use App\Models\ApartmentRental;
use App\Models\PaymentTransaction;
use App\Models\SystemData;
use App\Models\User;
use App\Services\PaystackService;
use Illuminate\Support\Facades\DB;

class ApartmentRentalService
{
    /**
     * Rent out an apartment.
     *
     * @param PaymentTransaction $paymentTransaction
     * @return void
     */
    public static function serve(PaymentTransaction $paymentTransaction)
    {
        $user = User::findOrFail($paymentTransaction->metadata['user_id']);
        $apartment = $paymentTransaction->model;

        $durationInDays = $apartment->apartmentDuration()->value('duration_in_days');

        // Check if there is an active rental for the user on the apartment
        $lastRental = ApartmentRental::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($apartment)
            ->active()
            ->latest('expired_at')
            ->first();

        $apartmentRental = new ApartmentRental();
        $apartmentRental->user()->associate($user);
        $apartmentRental->apartment()->associate($apartment);
        $apartmentRental->paymentTransaction()->associate($paymentTransaction);
        $apartmentRental->started_at = $lastRental?->expired_at ?? (
            $paymentTransaction->metadata['booking_start_date'] ?? now()
        );
        $apartmentRental->expired_at = $apartmentRental->started_at->addDays($durationInDays);
        $apartmentRental->save();

        event(new TempWalletModifier($apartment->user, $paymentTransaction->truthy_amount));
    }

    /**
     * Renew an apartment's rent.
     *
     * @param ApartmentRental $apartmentRental
     * @param PaystackService $paystackService
     * @return void
     */
    public static function renew(ApartmentRental $apartmentRental, PaystackService $paystackService)
    {
        DB::beginTransaction();

        $user = $apartmentRental->user;
        $apartment = $apartmentRental->apartment;

        $serviceCharge = (float) SystemData::query()->where('title', 'Service Charge')->value('content');
        $amountToPay = $apartment->price + (($serviceCharge / 100) * $apartment->price);

        // Create the payment transaction
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->user()->associate($user);
        $paymentTransaction->model()->associate($apartment);
        $paymentTransaction->payment_purpose = PaymentPurpose::APARTMENTRENTAL;
        $paymentTransaction->payment_gateway = PaymentGateway::PAYSTACK;
        $paymentTransaction->amount = $amountToPay;
        $paymentTransaction->truthy_amount = $apartment->price;
        $paymentTransaction->currency = 'NGN';
        $paymentTransaction->reference = strtoupper('AR' . now()->timestamp . bin2hex(random_bytes(6)));
        $paymentTransaction->metadata = [
            'user_id' => $user->id,
            'user_type' => $user->getMorphClass(),
            'model_id' => $apartment->id,
            'model_type' => $apartment->getMorphClass(),
            'service_charge' => $serviceCharge,
        ];

        // attempt to charge the user's wallet
        $newWalletBalance = $user->wallet_balance - $apartment->price;
        if ($newWalletBalance >= 0) {
            $paymentTransaction->payment_gateway = PaymentGateway::WALLET;
            $paymentTransaction->payment_method = 'wallet';
            $paymentTransaction->status = PaymentStatus::SUCCESS;
            $paymentTransaction->narration = 'Approved';

            $user->wallet_balance = $newWalletBalance;
            $user->save();

            event(new WalletHistoryRecorder(
                $user,
                $paymentTransaction,
                "₦{$paymentTransaction->amount} deducted from your wallet for apartment rent renewal"
            ));

            static::serve($paymentTransaction);
        } else {
            $paymentCard = $user->paymentCards()->primary()->first();

            if ($paymentCard && $paymentCard->payment_gateway === PaymentGateway::PAYSTACK) {
                $paystackService->getFactory()->transaction->charge([
                    'reference' => $paymentTransaction->reference,
                    'amount' => (int) ($paymentTransaction->amount * 100),
                    'currency' => $paymentTransaction->currency,
                    'email' => $paymentTransaction->user->email,
                    'authorization_code' => $paymentCard->authorization_code,
                ]);
            }
        }

        $paymentTransaction->save();

        DB::commit();
    }
}
