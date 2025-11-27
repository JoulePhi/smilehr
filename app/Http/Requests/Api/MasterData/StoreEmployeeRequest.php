<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create master data employees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:255', 'unique:users,nik'], // DB max=255
            'religion' => ['required', 'string'],
            'birth_place' => ['required', 'string', 'max:255'], // matches DB
            'birth_date' => ['required', 'date'],
            'gender' => ['required', 'in:male,female'],

            'phone' => ['required', 'string', 'max:255'], // DB max=255
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],

            'address' => ['required', 'string'],
            'domicile_address' => ['required', 'string'],
            'last_education' => ['required', 'string', 'max:255'], // matches DB

            'id_card_number' => ['required', 'string', 'max:50'],

            'branch_office_id' => ['required', 'exists:branch_offices,id'], // FIXED
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'cost_center_id' => ['required', 'exists:cost_centers,id'],

            'join_date' => ['required', 'date'],
            'employment_status' => ['required', 'in:permanent,contract,other'],
            'contract_start' => ['nullable', 'date'],
            'contract_end' => ['nullable', 'date'],

            'late_deduction' => ['required', 'numeric'],
            'late_tolerance' => ['required', 'numeric'],
            'allow_remote_attendance' => ['required', 'boolean'],
            'allow_branch_hopping' => ['required', 'boolean'],
            'check_in_mode' => ['required', 'in:card_and_photo,card_only'],

            'profile_image' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:2048'],


            // USER_FINANCIALS TABLE ------------------------

            'npwp' => ['required', 'string', 'max:255'],
            'tax_type' => ['required', 'string', 'max:255'],
            'tax_deduction_amount' => ['required', 'numeric'],
            'tax_allowance_percent' => ['required', 'numeric'],

            'bank_name' => ['required', 'string', 'max:255'],
            'bank_account_number' => ['required', 'string', 'max:255'],

            'bpjs_tk_number' => ['nullable', 'string', 'max:255'],
            'bpjs_kes_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
