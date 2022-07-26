<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PENDING()
 * @method static static CLOSED()
 * @method static static APPROVED()
 * @method static static DECLINED()
 */
final class LandlordRequestStatuses extends Enum
{
    public const PENDING = 'pending';
    public const CLOSED = 'closed';
    public const APPROVED = 'approved';
    public const DECLINED = 'declined';
}
