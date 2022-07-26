<?php

namespace App\Http\Requests\User\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UpdateUserPasswordRequest extends FormRequest
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
            'old_password' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, $this->user()->password)) {
                        $fail('Incorrect password provided.');
                    }
                },
            ],
            'new_password' => [
                'required',
                'confirmed',
                Password::default(),
            ],
            'callbackUrl' => 'required|url',
        ];
    }
}
