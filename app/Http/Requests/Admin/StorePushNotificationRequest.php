<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePushNotificationRequest extends FormRequest
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
            'subject' => 'required|string|max:191',
            'message' => 'required|string',
            'send_at' => 'required|date|after:now',
            'send_via_mail' => 'required|boolean',
            'send_via_system' => 'required|boolean',
            'user_ids' => 'required|array',
            'user_ids.*' => [
                'string',
                Rule::when($this->user_ids && $this->user_ids[0] !== '*', 'exists:users,id'),
            ],
        ];
    }
}
