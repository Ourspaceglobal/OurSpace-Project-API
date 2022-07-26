<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreLandlordRequestRequest extends FormRequest
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
            'note' => 'nullable|string|max:1000',
            'national_identity_number' => 'nullable|string|max:191',
            'kycs' => 'required|array|min:1|max:5',
            'kycs.*' => 'file|max:2000',
        ];
    }
}
