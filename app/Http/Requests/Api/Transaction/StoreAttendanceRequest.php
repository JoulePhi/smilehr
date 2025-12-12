<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create transactions attendances');
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
            '*.date_in' => 'required|date',
            '*.date_out' => 'required|date',
            '*.time_in' => 'required|date_format:H:i',
            '*.time_out' => 'required|date_format:H:i',
            '*.remarks' => 'nullable|string',
        ];
    }
}
