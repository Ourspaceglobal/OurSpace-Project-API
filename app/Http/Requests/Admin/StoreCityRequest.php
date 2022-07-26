<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCityRequest extends FormRequest
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
            'state_id' => 'required|exists:states,id',
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('cities')->where('state_id', $this->state_id),
            ],
        ];
    }
}
