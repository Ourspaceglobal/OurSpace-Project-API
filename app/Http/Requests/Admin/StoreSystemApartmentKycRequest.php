<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSystemApartmentKycRequest extends FormRequest
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
            'name' => 'required|string|max:191|unique:system_apartment_kycs',
            'datatype_id' => 'required|exists:datatypes,id',
            'description' => 'required|string|max:191',
            'is_required' => 'required|boolean',
        ];
    }
}
