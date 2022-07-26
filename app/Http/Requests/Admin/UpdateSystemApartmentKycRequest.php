<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSystemApartmentKycRequest extends FormRequest
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
            'datatype_id' => 'required|exists:datatypes,id',
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('system_apartment_kycs')->ignoreModel($this->system_apartment_kyc),
            ],
            'description' => 'required|string|max:191',
            'is_required' => 'required|boolean',
        ];
    }
}
