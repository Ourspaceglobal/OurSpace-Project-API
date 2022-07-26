<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocalGovernmentRequest extends FormRequest
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
            'city_id' => 'required|exists:cities,id',
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('local_governments')->where('city_id', $this->city_id)
                    ->ignoreModel($this->local_government),
            ],
        ];
    }
}
