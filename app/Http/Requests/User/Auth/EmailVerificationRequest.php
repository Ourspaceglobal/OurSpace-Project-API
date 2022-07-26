<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($code = $this->code) {
            $verification = $this->user()->emailVerificationCode;

            if (!$verification) {
                return false;
            }

            if ($verification->is_expired) {
                return false;
            }

            if ($verification->code !== $code) {
                return false;
            }
        } else {
            if (! hash_equals((string) $this->id, (string) $this->user()->getKey())) {
                return false;
            }

            if (! hash_equals((string) $this->hash, sha1($this->user()->getEmailForVerification()))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Fulfill the email verification request.
     *
     * @return void
     */
    public function fulfill()
    {
        if (! $this->user()->hasVerifiedEmail()) {
            $this->user()->markEmailAsVerified();

            event(new Verified($this->user()));
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        return $validator;
    }
}
