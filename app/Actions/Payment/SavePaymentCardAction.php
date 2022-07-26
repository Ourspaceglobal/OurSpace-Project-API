<?php

namespace App\Actions\Payment;

use App\Models\PaymentCard;
use App\Type\PaymentCardType;
use Spatie\QueueableAction\QueueableAction;

class SavePaymentCardAction
{
    use QueueableAction;

    /**
     * Create a new action instance.
     *
     * @param PaymentCard $paymentCard
     * @return void
     */
    public function __construct(public PaymentCard $paymentCard)
    {
        // Prepare the action for execution, leveraging constructor injection.
    }

    /**
     * Execute the action.
     *
     * @param PaymentCardType $paymentCardType
     * @return void
     */
    public function execute(PaymentCardType $paymentCardType)
    {
        $paymentCard = $this->paymentCard
            ->where('user_id', $paymentCardType->user_id)
            ->where('user_type', $paymentCardType->user_type)
            ->where('first_6digits', $paymentCardType->first6digits)
            ->where('last_4digits', $paymentCardType->last4digits)
            ->where('expiry_month', $paymentCardType->expiryMonth)
            ->where('expiry_year', $paymentCardType->expiryYear)
            ->firstOrNew([]);
        $paymentCard->user_id = $paymentCardType->user_id;
        $paymentCard->user_type = $paymentCardType->user_type;
        $paymentCard->authorization_code = $paymentCardType->authorizationCode;
        $paymentCard->card_type = $paymentCardType->cardType;
        $paymentCard->first_6digits = $paymentCardType->first6digits;
        $paymentCard->last_4digits = $paymentCardType->last4digits;
        $paymentCard->expiry_month = $paymentCardType->expiryMonth;
        $paymentCard->expiry_year = $paymentCardType->expiryYear;
        $paymentCard->bank = $paymentCardType->bank;
        $paymentCard->country_code = $paymentCardType->countryCode;
        $paymentCard->account_name = $paymentCardType->accountName;
        $paymentCard->payment_gateway = $paymentCardType->paymentGateway;
        $paymentCard->save();
    }
}
