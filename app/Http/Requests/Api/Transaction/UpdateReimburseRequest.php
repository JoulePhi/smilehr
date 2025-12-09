<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReimburseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update transactions reimbursements');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'required', 'numeric'],
            'date' => ['sometimes', 'required', 'date'],
            'remarks' => ['nullable', 'string'],
            'is_paid' => ['nullable', 'boolean'],
        ];
    }
}
