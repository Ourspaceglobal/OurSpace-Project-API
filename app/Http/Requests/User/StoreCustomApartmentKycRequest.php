<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomApartmentKycRequest extends FormRequest
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
            'system_apartment_kyc_ids' => 'required|array|min:1',
            'system_apartment_kyc_ids.*' => [
                Rule::exists('system_apartment_kycs', 'id')->where('is_required', false),
            ],
        ];
    }
}
