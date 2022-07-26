<?php

namespace App\Http\Requests\User\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    private const AGELIMIT = 10;

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
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('users')->ignoreModel($this->user()),
            ],
            'phone_number' => 'required|string|max:191',
            'country' => 'required|string|max:191',
            'state' => 'required|string|max:191',
            'gender' => 'required|string|in:male,female,other',
            'date_of_birth' => 'required|date|before:' . now()->subYears(self::AGELIMIT),
            'home_address' => 'required|string|max:191',
            'callbackUrl' => 'required|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date_of_birth.before' => 'We expect you to be more than ' . self::AGELIMIT . ' years old'
        ];
    }
}
