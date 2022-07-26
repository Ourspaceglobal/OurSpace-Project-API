<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SUCCESS()
 * @method static static FAIL()
 * @method static static PENDING()
 * @method static static CANCEL()
 * @method static static REJECT()
 */
final class PaymentStatus extends Enum
{
    public const PENDING = 'pending';
    public const SUCCESS = 'success';
    public const FAIL = 'fail';
    public const CANCEL = 'cancel';
    public const REJECT = 'reject';
}
