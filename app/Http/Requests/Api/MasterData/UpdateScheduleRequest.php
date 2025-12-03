<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data schedules');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shift_in_date' => ['sometimes', 'required', 'date'],
            'shift_out_date' => ['sometimes', 'required', 'date', 'after_or_equal:shift_in_date'],
            'shift_in' => ['sometimes', 'required', 'date_format:H:i'],
            'shift_out' => ['sometimes', 'required', 'date_format:H:i'],
            'remarks' => ['nullable', 'string'],
            'is_approved' => ['nullable', 'boolean'],
            'is_validated' => ['nullable', 'boolean'],
        ];
    }
}
