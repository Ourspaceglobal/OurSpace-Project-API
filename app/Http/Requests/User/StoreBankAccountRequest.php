<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBankAccountRequest extends FormRequest
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
        return [
            'bank_name' => 'required|string|max:191',
            'account_number' => [
                'required',
                'string',
                'max:191',
                Rule::unique('bank_accounts')
                    ->where('user_id', $this->user()->id)
                    ->where('account_name', $this->account_name)
            ],
            'account_name' => 'required|string|max:191',
        ];
    }
}
