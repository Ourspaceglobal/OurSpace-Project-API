<?php

namespace App\Services;

use App\Actions\Payment\SavePaymentCardAction;
use App\Enums\PaymentGateway;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\PaymentTransaction;
use App\Type\PaymentCardType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yabacon\Paystack;

class PaystackService
{
    protected Paystack $paystack;

    /**
     * Prepare the paystack library.
     *
     * @param SavePaymentCardAction $savePaymentCardAction
     * @return void
     */
    public function __construct(public SavePaymentCardAction $savePaymentCardAction)
    {
        $this->paystack = new Paystack(config('paystack.secret_key'));
    }

    /**
     * Get the Paystack library service.
     *
     * @return Paystack
     */
    public function getFactory()
    {
        return $this->paystack;
    }

    /**
     * Process the paystack transaction.
     *
     * @param string $reference
     * @return void
     * @throws \InvalidArgumentException
     * @throws \PDOException
     * @throws \Exception
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function process(string $reference)
    {
        // Verify the transaction
        $transaction = $this->getFactory()->transaction->verify([
            'reference' => $reference,
        ]);

        // Verify that the transaction was successfully registered in our Paystack account
        if ($transaction->status === false) {
            exit();
        }

        try {
            DB::beginTransaction();

            // Get the payment transaction
            $paymentTransaction = PaymentTransaction::query()
                ->where('reference', $reference)
                ->where('payment_gateway', PaymentGateway::PAYSTACK)
                ->firstOrFail();

            $paymentTransaction->payment_method = $transaction->data->channel;
            $paymentTransaction->status = $transaction->data->status;
            $paymentTransaction->narration = $transaction->data->gateway_response;
            $paymentTransaction->save();

            DB::commit();

            // Exit if the payment transaction is not successful
            if ($paymentTransaction->status !== PaymentStatus::SUCCESS) {
                exit();
            }

            // Build the payment card type class
            if ($transaction->data->authorization->channel === 'card') {
                $paymentCardType = $this->buildPaymentCardType($transaction, $paymentTransaction);
                $this->savePaymentCardAction->onQueue()->execute($paymentCardType);
            }

            // Proceed to main course of action
            DB::beginTransaction();

            switch ($paymentTransaction->payment_purpose) {
                case PaymentPurpose::WALLETFUND:
                    \App\Services\User\FundWalletService::serve($paymentTransaction);
                    break;

                case PaymentPurpose::APARTMENTRENTAL:
                    \App\Services\User\ApartmentRentalService::serve($paymentTransaction);
                    break;

                default:
                    break;
            }

            DB::commit();
        } catch (\InvalidArgumentException $e) {
            Log::error('An invalid argument exception occurred.', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            exit();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Model not found.', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            exit();
        } catch (\PDOException $e) {
            Log::error('A database query exception occurred.', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            exit();
        } catch (\Exception $e) {
            Log::error('An exception occurred.', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            exit();
        }
    }

    /**
     * Build the payment card type class.
     *
     * @param mixed $transaction
     * @param PaymentTransaction $paymentTransaction
     * @return PaymentCardType
     */
    public function buildPaymentCardType($transaction, PaymentTransaction $paymentTransaction)
    {
        $paymentCardType = new PaymentCardType();
        $paymentCardType->user_id = $paymentTransaction->metadata['user_id'];
        $paymentCardType->user_type = $paymentTransaction->metadata['user_type'];
        $paymentCardType->authorizationCode = $transaction->data->authorization->authorization_code;
        $paymentCardType->cardType = $transaction->data->authorization->card_type;
        $paymentCardType->first6digits = $transaction->data->authorization->bin;
        $paymentCardType->last4digits = $transaction->data->authorization->last4;
        $paymentCardType->expiryMonth = $transaction->data->authorization->exp_month;
        $paymentCardType->expiryYear = $transaction->data->authorization->exp_year;
        $paymentCardType->bank = $transaction->data->authorization->bank;
        $paymentCardType->countryCode = $transaction->data->authorization->country_code;
        $paymentCardType->accountName = $transaction->data->authorization->account_name;
        $paymentCardType->paymentGateway = PaymentGateway::PAYSTACK;

        return $paymentCardType;
    }
}
