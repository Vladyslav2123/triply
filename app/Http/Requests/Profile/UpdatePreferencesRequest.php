<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'preferred_language' => ['required', 'string', 'max:10'],
            'preferred_currency' => ['required', 'string', 'max:3'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'preferred_language.required' => 'Мова є обов\'язковою',
            'preferred_language.string' => 'Мова має бути текстовим значенням',
            'preferred_language.max' => 'Мова не може перевищувати 10 символів',
            'preferred_currency.required' => 'Валюта є обов\'язковою',
            'preferred_currency.string' => 'Валюта має бути текстовим значенням',
            'preferred_currency.max' => 'Валюта не може перевищувати 3 символи',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('preferred_language')) {
            $this->merge([
                'preferred_language' => strtolower($this->preferred_language),
            ]);
        }

        if ($this->has('preferred_currency')) {
            $this->merge([
                'preferred_currency' => strtoupper($this->preferred_currency),
            ]);
        }
    }
}
