<?php

namespace App\Http\Requests\Profile;

use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
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
            // Основна інформація
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', new Enum(Gender::class)],
            'is_superhost' => ['nullable', 'boolean'],
            'response_speed' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],

            // Робота та освіта
            'work' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:50'],
            'school' => ['nullable', 'string', 'max:50'],
            'education_level' => ['nullable', new Enum(EducationLevel::class)],

            // Подорожі
            'dream_destination' => ['nullable', 'string', 'max:50'],
            'next_destinations' => ['nullable', 'array'],
            'next_destinations.*' => ['string', 'max:50'],
            'travel_history' => ['nullable', 'boolean'],
            'favorite_travel_type' => ['nullable', 'string', 'max:30'],

            // Особисті дані
            'time_spent_on' => ['nullable', 'string', 'max:50'],
            'useless_skill' => ['nullable', 'string', 'max:50'],
            'pets' => ['nullable', 'string', 'max:50'],
            'birth_decade' => ['nullable', 'boolean'],
            'favorite_high_school_song' => ['nullable', 'string', 'max:50'],
            'fun_fact' => ['nullable', 'string'],
            'obsession' => ['nullable', 'string', 'max:50'],
            'biography_title' => ['nullable', 'string', 'max:50'],

            // Мови та інтереси
            'languages' => ['nullable', 'array'],
            'languages.*' => [Rule::in(Language::values())],
            'about' => ['nullable', 'string'],
            'interests' => ['nullable', 'array'],
            'interests.*' => [Rule::in(Interest::values())],

            // Соціальні мережі
            'facebook_url' => ['nullable', 'url', 'max:100'],
            'instagram_url' => ['nullable', 'url', 'max:100'],
            'twitter_url' => ['nullable', 'url', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:100'],

            // Налаштування
            'email_notifications' => ['nullable', 'boolean'],
            'sms_notifications' => ['nullable', 'boolean'],
            'preferred_language' => ['nullable', 'string', 'max:10'],
            'preferred_currency' => ['nullable', 'string', 'max:3'],
        ];
    }
}
