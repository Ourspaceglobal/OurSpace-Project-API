<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static TENANT()
 * @method static static LANDLORD()
 */
final class UserType extends Enum
{
    public const TENANT = 'tenant';
    public const LANDLORD = 'landlord';
}
