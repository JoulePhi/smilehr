<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create master data schedules');
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
            '*.shift_in_date' => 'required|date',
            '*.shift_in' => 'required',
            '*.shift_out_date' => 'required|date',
            '*.shift_out' => 'required',
            '*.remarks' => 'nullable|string',
        ];
    }
}
