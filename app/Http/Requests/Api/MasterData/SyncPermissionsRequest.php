<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class SyncPermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'permissions' => [
                'required',
                'array',
                'min:0',
                'max:1000',
            ],
            'permissions.*' => [
                'required',
                'exists:permissions,id',
                'distinct',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'permissions.required' => 'Permissions array is required.',
            'permissions.max' => 'Cannot sync more than 1000 permissions at once.',
            'permissions.*.exists' => 'Selected permission does not exist.',
            'permissions.*.distinct' => 'Duplicate permission IDs are not allowed.',
        ];
    }
}
