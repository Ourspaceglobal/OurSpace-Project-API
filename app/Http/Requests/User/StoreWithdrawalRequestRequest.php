<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWithdrawalRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = $this->user();

        return [
            'bank_account_id' => [
                'required',
                Rule::exists('bank_accounts', 'id')->where('user_id', $user->id),
            ],
            'amount' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($user) {
                    if (($user->wallet_balance - $value) < 0) {
                        $fail("The {$attribute} cannot be higher than your wallet balance ($user->wallet_balance).");
                    }
                },
            ],
            'reason' => 'nullable|string|max:191',
        ];
    }
}
