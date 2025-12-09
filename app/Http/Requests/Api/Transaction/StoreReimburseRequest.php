<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreReimburseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create transactions reimbursements');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            '*' => 'required|array',
            '*.employee_id' => 'required|exists:users,id',
            '*.amount' => 'required|numeric',
            '*.date' => 'required|date',
            '*.remarks' => 'nullable|string',
            '*.is_paid' => 'nullable|boolean',
        ];
    }
}
