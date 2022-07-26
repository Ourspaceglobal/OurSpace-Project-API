<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApartmentLocationRequest extends FormRequest
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
            'house_number' => 'required|string|max:191',
            'street' => 'required|string|max:191',
            'landmark' => 'nullable|string|max:191',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => [
                'nullable',
                Rule::exists('cities', 'id')->where('state_id', $this->state_id),
            ],
            'local_government_id' => [
                'nullable',
                Rule::exists('local_governments', 'id')->where('city_id', $this->city_id),
            ],
        ];
    }
}
