<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data employees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('employee');
        return [
            'name' => ['sometimes', 'string', 'max:255'],

            'nik' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'nik')->ignore($userId),
            ],

            'religion' => ['sometimes', 'string'],

            'birth_place' => ['sometimes', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'date'],
            'gender' => ['sometimes', 'in:male,female'],

            'phone' => ['sometimes', 'string', 'max:255'],

            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'address' => ['sometimes', 'string'],
            'domicile_address' => ['sometimes', 'string'],
            'last_education' => ['sometimes', 'string', 'max:255'],
            'id_card_number' => ['sometimes', 'string', 'max:50'],

            'branch_office_id' => ['sometimes', 'exists:branch_offices,id'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'position_id' => ['sometimes', 'exists:positions,id'],
            'cost_center_id' => ['sometimes', 'exists:cost_centers,id'],

            'join_date' => ['sometimes', 'date'],
            'employment_status' => ['sometimes', 'in:permanent,contract,other'],
            'contract_start' => ['nullable', 'date'],
            'contract_end' => ['nullable', 'date'],

            'late_deduction' => ['sometimes', 'numeric'],
            'late_tolerance' => ['sometimes', 'numeric'],
            'allow_remote_attendance' => ['sometimes', 'boolean'],
            'allow_branch_hopping' => ['sometimes', 'boolean'],
            'check_in_mode' => ['sometimes', 'in:card_and_photo,card_only'],

            'profile_image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],


            // USER_FINANCIALS TABLE --------------------------------------

            'npwp' => ['nullable', 'string', 'max:255'],
            'tax_type' => ['nullable', 'string', 'max:255'],
            'tax_deduction_amount' => ['sometimes', 'numeric'],
            'tax_allowance_percent' => ['sometimes', 'numeric'],

            'bank_name' => ['sometimes', 'string', 'max:255'],
            'bank_account_number' => ['sometimes', 'string', 'max:255'],

            'bpjs_tk_number' => ['nullable', 'string', 'max:255'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
