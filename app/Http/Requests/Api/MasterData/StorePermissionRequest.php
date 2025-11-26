<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user()->can('create master data permissions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name',
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
            'name.required' => 'Permission name is required.',
            'name.unique' => 'A permission with this name already exists.',
            'name.regex' => 'Permission name may only contain lowercase letters, numbers, spaces, hyphens, periods, underscores, and colons.',
            'guard_name.in' => 'Guard must be one of: web, api, sanctum.',
        ];
    }
}
