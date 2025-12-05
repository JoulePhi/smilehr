<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update transactions leaves');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'required', 'date'],
            'type' => ['sometimes', 'required', 'in:leave,sick,permission'],
            'remarks' => ['nullable', 'string'],
            'is_approved' => ['nullable', 'boolean'],
            'is_validated' => ['nullable', 'boolean'],
        ];
    }
}
