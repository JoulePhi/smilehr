<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit master data home');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
            'image' => [
                'sometimes',
                'image',
                'mimes:jpeg,png,jpg',
                'max:5120',
            ],
            'link_url' => [
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }
}
