<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class BulkOperationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('roles.bulk');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'role_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'role_ids.*' => [
                'required',
                'exists:roles,id',
                'distinct',
            ],
            'permission_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'permission_ids.*' => [
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
            'role_ids.required' => 'Role IDs array is required.',
            'role_ids.max' => 'Cannot process more than 100 roles at once.',
            'role_ids.*.exists' => 'Selected role does not exist.',
            'permission_ids.required' => 'Permission IDs array is required.',
            'permission_ids.max' => 'Cannot process more than 100 permissions at once.',
            'permission_ids.*.exists' => 'Selected permission does not exist.',
            '*.distinct' => 'Duplicate IDs are not allowed.',
        ];
    }
}