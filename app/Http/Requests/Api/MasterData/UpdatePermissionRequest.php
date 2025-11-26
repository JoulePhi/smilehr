<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permission),
                'regex:/^[a-z0-9_\-\.\s:]+$/',
            ],
            'guard_name' => [
                'sometimes',
                'string',
                'in:web,api,sanctum',
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
            'name.unique' => 'A permission with this name already exists.',
            'name.regex' => 'Permission name may only contain lowercase letters, numbers, spaces, hyphens, periods, underscores, and colons.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
        ];
    }
}
