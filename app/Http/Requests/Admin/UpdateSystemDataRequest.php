<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemDataRequest extends FormRequest
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
        $rules = explode('|', $this->system_datum->datatype->rule);

        return [
            'content' => collect(array_merge([
                'required',
                $rules,
            ]))->flatten()->toArray(),
        ];
    }
}
