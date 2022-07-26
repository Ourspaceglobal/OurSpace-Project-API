<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('roles')->where('guard_name', 'api_admin'),
            ],
            'permissions' => 'required|array|min:1',
            'permissions.*' => [
                Rule::exists('permissions', 'id')->where('guard_name', 'api_admin'),
            ],
        ];
    }
}
