<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data salaries');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'salaries' => ['required', 'array', 'min:1'],

            'salaries.*.employee_id' => ['required', 'string', 'exists:users,id'],
            'salaries.*.basic_salary' => ['required', 'numeric', 'min:0'],
            'salaries.*.regular_dues' => ['nullable', 'numeric', 'min:0'],

            'salaries.*.fixed_allowance' => ['nullable', 'numeric', 'min:0'],
            'salaries.*.other_allowance' => ['nullable', 'numeric', 'min:0'],
            'salaries.*.daily_allowance' => ['nullable', 'numeric', 'min:0'],

            'salaries.*.bpjs_jht_user' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.bpjs_health_user' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.bpjs_jp_user' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.others_user' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'salaries.*.bpjs_jht_company' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.bpjs_health_company' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.bpjs_jp_company' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.others_company' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'salaries.*.bpjs_jkm' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'salaries.*.bpjs_jkk' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
