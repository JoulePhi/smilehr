<?php

namespace App\Http\Requests\Api\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update transactions attendances');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date_in' => ['sometimes', 'required', 'date'],
            'date_out' => ['sometimes', 'required', 'date'],
            'time_in' => ['sometimes', 'required'],
            'time_out' => ['sometimes', 'required'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
