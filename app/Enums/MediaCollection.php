<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static AVATAR()
 * @method static static FEATUREDIMAGE()
 * @method static static PROOFOFPAYMENT()
 * @method static static ICON()
 * @method static static KYC()
 * @method static static ATTACHMENT()
 * @method static static GALLERY()
 */
final class MediaCollection extends Enum
{
    public const AVATAR = 'avatar';
    public const FEATUREDIMAGE = 'featured_image';
    public const PROOFOFPAYMENT = 'proof_of_payment';
    public const ICON = 'icon';
    public const KYC = 'kyc';
    public const ATTACHMENT = 'attachment';
    public const GALLERY = 'gallery';
}
