<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterApartmentRequest extends FormRequest
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
            'search' => 'nullable|string',
            'user' => 'nullable|string',
            'date_added' => 'nullable|date_format:m/Y',
            'categories' => 'nullable|string',
            'sub_categories' => 'nullable|string',
            'amenities' => 'nullable|string',
            'price_range' => 'nullable|string',
            'ratings' => 'nullable|string',
            'available_date' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'date_added.date_format' => 'Incorrect format for date filter. Expects month/year.',
        ];
    }
}
