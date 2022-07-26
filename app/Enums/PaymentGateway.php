<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PAYSTACK()
 * @method static static WALLET()
 */
final class PaymentGateway extends Enum
{
    public const PAYSTACK = 'paystack';
    public const WALLET = 'wallet';
}
