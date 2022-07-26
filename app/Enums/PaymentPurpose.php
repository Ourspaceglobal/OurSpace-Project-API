<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static WALLETFUND()
 * @method static APARTMENTRENTAL()
 * @method static WITHDRAWALREQUEST()
 * @method static WALLETFUNDINGREQUEST()
 * @method static APARTMENTBOOKINGCANCELLATION()
 */
final class PaymentPurpose extends Enum
{
    public const WALLETFUND = 'wallet_fund';
    public const APARTMENTRENTAL = 'apartment_rental';
    public const WITHDRAWALREQUEST = 'withdrawal_request';
    public const WALLETFUNDINGREQUEST = 'wallet_funding_request';
    public const APARTMENTBOOKINGCANCELLATION = 'apartment_booking_cancellation';
}
