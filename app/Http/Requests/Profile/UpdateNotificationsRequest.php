<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
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
            'email_notifications' => ['required', 'boolean'],
            'sms_notifications' => ['required', 'boolean'],
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
            'email_notifications.required' => 'Налаштування email сповіщень обов\'язкове',
            'email_notifications.boolean' => 'Налаштування email сповіщень має бути логічним значенням',
            'sms_notifications.required' => 'Налаштування SMS сповіщень обов\'язкове',
            'sms_notifications.boolean' => 'Налаштування SMS сповіщень має бути логічним значенням',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string boolean values to actual booleans
        if ($this->has('email_notifications')) {
            $this->merge([
                'email_notifications' => filter_var($this->email_notifications, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }

        if ($this->has('sms_notifications')) {
            $this->merge([
                'sms_notifications' => filter_var($this->sms_notifications, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            ]);
        }
    }
}
