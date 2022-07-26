<?php

namespace App\Http\Requests\Admin\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
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
            'first_name' => 'required|string|max:191',
            'last_name' => 'required|string|max:191',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('admins')->ignoreModel($this->user()),
            ],
            'phone_number' => 'required|string|max:191',
            'callbackUrl' => 'required|url',
        ];
    }
}
