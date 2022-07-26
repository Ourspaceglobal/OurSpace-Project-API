<?php

namespace App\Traits;

use App\Models\EmailVerificationCode;

trait EmailVerificationCodeTrait
{
    /**
     * Generate code for email verification.
     *
     * @return int
     */
    public function generateEmailVerificationCode(): int
    {
        $this->emailVerificationCode()->delete();

        $emailVerificationCode = new EmailVerificationCode();
        $emailVerificationCode->user()->associate($this);
        $emailVerificationCode->save();

        return $emailVerificationCode->code;
    }
}
