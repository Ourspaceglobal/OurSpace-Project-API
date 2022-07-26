<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreApartmentContactRequest extends FormRequest
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
            'name' => 'nullable|string|max:191',
            'email' => 'nullable|string|email:rfc,dns',
            'phone_number_1' => 'nullable|string',
            'phone_number_2' => 'nullable|string',
        ];
    }
}
