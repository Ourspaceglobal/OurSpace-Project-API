<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubCategoryRequest extends FormRequest
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
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sub_categories')->where(function ($query) {
                    return $query->where('category_id', $this->category_id);
                }),
            ],
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|image|max:2000',
        ];
    }
}
