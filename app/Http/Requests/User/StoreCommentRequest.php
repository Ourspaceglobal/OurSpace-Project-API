<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
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
            'model_type' => 'required|string|in:post',
            'model_id' => [
                'required',
                Rule::when($this->model === 'post', 'exists:posts,id'),
            ],
            'comment' => 'required|string|max:2000',
            'parent_id' => [
                'nullable',
                Rule::when($this->model === 'post', [
                    Rule::exists('posts', 'id')->where('model_type', 'post')
                ]),
            ],
        ];
    }
}
