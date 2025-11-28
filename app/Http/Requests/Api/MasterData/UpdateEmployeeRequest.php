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
        $user = $this->route('employee');
        $userId = $user ? $user->id : null;
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'nik' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'nik')->ignore($userId),
            ],

            'religion' => ['nullable', 'string'],

            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female'],

            'phone' => ['nullable', 'string', 'max:255'],

            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'address' => ['nullable', 'string'],
            'domicile_address' => ['nullable', 'string'],
            'last_education' => ['nullable', 'string', 'max:255'],
            'id_card_number' => ['nullable', 'string', 'max:50'],

            'branch_office_id' => ['nullable', 'exists:branch_offices,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],

            'join_date' => ['nullable', 'date'],
            'employment_status' => ['nullable', 'in:permanent,contract,other'],
            'contract_start' => ['nullable', 'date'],
            'contract_end' => ['nullable', 'date'],

            'late_deduction' => ['nullable', 'numeric'],
            'late_tolerance' => ['nullable', 'numeric'],
            'allow_remote_attendance' => ['nullable', 'boolean'],
            'allow_branch_hopping' => ['nullable', 'boolean'],
            'check_in_mode' => ['nullable', 'in:card_and_photo,card_only'],

            'profile_image' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],


            // USER_FINANCIALS TABLE --------------------------------------

            'npwp' => ['nullable', 'string', 'max:255'],
            'tax_type' => ['nullable', 'string', 'max:255'],
            'tax_deduction_amount' => ['nullable', 'numeric'],
            'tax_allowance_percent' => ['nullable', 'numeric'],

            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],

            'bpjs_tk_number' => ['nullable', 'string', 'max:255'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
