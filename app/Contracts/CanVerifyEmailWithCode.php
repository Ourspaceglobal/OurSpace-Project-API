<?php

namespace App\Contracts;

interface CanVerifyEmailWithCode
{
    /**
     * Generate code for email verification.
     *
     * @return int
     */
    public function generateEmailVerificationCode(): int;
}
