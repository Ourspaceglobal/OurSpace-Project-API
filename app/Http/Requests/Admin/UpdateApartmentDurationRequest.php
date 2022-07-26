<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApartmentDurationRequest extends FormRequest
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
            'period' => [
                'required',
                'string',
                'max:191',
                Rule::unique('apartment_durations')->ignoreModel($this->apartment_duration),
            ],
            'duration_in_days' => 'required|numeric',
        ];
    }
}
