<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
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
            'model_type' => 'required|in:apartment,post,user',
            'model_id' => [
                'required',
                Rule::when($this->model === 'apartment', 'exists:apartments,id'),
                Rule::when($this->model === 'post', 'exists:posts,id'),
                Rule::when($this->model === 'user', 'exists:users,id'),
            ],
            'comment' => 'required|string|max:5000',
            'rating' => 'required|integer|between:1,5',
        ];
    }
}
