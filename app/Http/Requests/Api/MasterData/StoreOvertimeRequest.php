<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreOvertimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create master data overtimes');
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
            '*.is_special' => 'required|boolean',
            '*.shift_in_date' => 'required|date',
            '*.shift_in' => 'required',
            '*.shift_out_date' => 'required|date',
            '*.shift_out' => 'required',
            '*.remarks' => 'nullable|string',
        ];
    }
}
