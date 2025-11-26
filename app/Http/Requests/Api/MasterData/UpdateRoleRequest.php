<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit roles');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role),
                'regex:/^[a-zA-Z0-9_\-\s]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
            ],
            'permissions' => [
                'sometimes',
                'array',
                'max:100',
            ],
            'permissions.*' => [
                'exists:permissions,id',
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
            'name.unique' => 'A role with this name already exists.',
            'name.regex' => 'Role name may only contain letters, numbers, spaces, hyphens, and underscores.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
            'permissions.*.exists' => 'Selected permission does not exist.',
            'permissions.max' => 'Cannot select more than 100 permissions.',
        ];
    }
}
