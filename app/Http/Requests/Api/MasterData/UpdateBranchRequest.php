<?php

namespace App\Http\Requests\Api\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update master data branches');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'npwp' => 'nullable|string|max:50',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'radius' => 'nullable|numeric',
            'phone_number' => 'nullable|string|max:20',
            'work_hour_in_weekday' => 'nullable|date_format:H:i:s',
            'work_hour_out_weekday' => 'nullable|date_format:H:i:s',
            'work_hour_in_weekend' => 'nullable|date_format:H:i:s',
            'work_hour_out_weekend' => 'nullable|date_format:H:i:s',
        ];
    }
}
