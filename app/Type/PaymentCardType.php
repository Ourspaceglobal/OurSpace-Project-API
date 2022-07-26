<?php

namespace App\Type;

use App\Enums\PaymentGateway;

class PaymentCardType
{
    /**
     * The owner of the card.
     *
     * @var mixed
     */
    public $user_id = null;

    /**
     * The user type (user or admin)
     *
     * @var string|null
     */
    public ?string $user_type = null;

    /**
     * The authorization code or token.
     *
     * @var string|null
     */
    public ?string $authorizationCode = null;

    /**
     * The card type (visa, mastercard, etc).
     *
     * @var string|null
     */
    public ?string $cardType = null;

    /**
     * The first 6 digits on the card.
     *
     * @var int|null
     */
    public ?int $first6digits = null;

    /**
     * The last 4 digits on the card.
     *
     * @var integer|null
     */
    public ?int $last4digits = null;

    /**
     * The expiry month of the card.
     *
     * @var int|null
     */
    public ?int $expiryMonth = null;

    /**
     * The expiry year of the card.
     *
     * @var integer|null
     */
    public ?int $expiryYear = null;

    /**
     * The bank owner of the card.
     *
     * @var string|null
     */
    public ?string $bank = null;

    /**
     * The card country.
     *
     * @var string|null
     */
    public ?string $countryCode = null;

    /**
     * The card owner.
     *
     * @var string|null
     */
    public ?string $accountName = null;

    /**
     * The payment gateway that passed the card.
     *
     * @var string|null
     */
    public ?string $paymentGateway = null;
}
