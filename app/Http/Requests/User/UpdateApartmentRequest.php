<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApartmentRequest extends FormRequest
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
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => [
                'required',
                Rule::exists('sub_categories', 'id')->where(function ($query) {
                    $query->where('category_id', $this->category_id);
                }),
            ],
            'price' => 'required|numeric',
            'apartment_duration_id' => 'required|exists:apartment_durations,id',
            'featured_image' => 'nullable|image',
        ];
    }
}
