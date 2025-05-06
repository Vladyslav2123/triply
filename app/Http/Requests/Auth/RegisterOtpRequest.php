<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterOtpRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/|unique:users,phone',
            'otp' => 'required|string|size:6',
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'email' => 'required|string|email|max:255|unique:users,email',
            'photo' => ['nullable', 'image', 'max:5120'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
