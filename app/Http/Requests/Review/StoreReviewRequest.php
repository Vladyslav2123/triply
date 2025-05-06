<?php

namespace App\Http\Requests\Review;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReviewRequest extends FormRequest
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
            'reservation_id' => [
                'required',
                'string',
                'exists:reservations,id',
                Rule::unique('reviews')->where('reviewer_id', auth()->id()),
            ],
            'cleanliness_rating' => 'required|integer|min:1|max:5',
            'accuracy_rating' => 'required|integer|min:1|max:5',
            'checkin_rating' => 'required|integer|min:1|max:5',
            'communication_rating' => 'required|integer|min:1|max:5',
            'location_rating' => 'required|integer|min:1|max:5',
            'value_rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
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
            // Reservation ID
            'reservation_id.required' => 'Бронювання обов\'язкове для відгуку.',
            'reservation_id.string' => 'ID бронювання має бути рядком.',
            'reservation_id.exists' => 'Вказане бронювання не існує.',
            'reservation_id.unique' => 'Ви вже залишили відгук для цього бронювання.',

            // Cleanliness Rating
            'cleanliness_rating.required' => 'Будь ласка, оцініть чистоту.',
            'cleanliness_rating.integer' => 'Оцінка чистоти має бути цілим числом.',
            'cleanliness_rating.min' => 'Оцінка чистоти має бути не менше 1.',
            'cleanliness_rating.max' => 'Оцінка чистоти має бути не більше 5.',

            // Accuracy Rating
            'accuracy_rating.required' => 'Будь ласка, оцініть точність опису.',
            'accuracy_rating.integer' => 'Оцінка точності має бути цілим числом.',
            'accuracy_rating.min' => 'Оцінка точності має бути не менше 1.',
            'accuracy_rating.max' => 'Оцінка точності має бути не більше 5.',

            // Check-in Rating
            'checkin_rating.required' => 'Будь ласка, оцініть процес заселення.',
            'checkin_rating.integer' => 'Оцінка заселення має бути цілим числом.',
            'checkin_rating.min' => 'Оцінка заселення має бути не менше 1.',
            'checkin_rating.max' => 'Оцінка заселення має бути не більше 5.',

            // Communication Rating
            'communication_rating.required' => 'Будь ласка, оцініть комунікацію.',
            'communication_rating.integer' => 'Оцінка комунікації має бути цілим числом.',
            'communication_rating.min' => 'Оцінка комунікації має бути не менше 1.',
            'communication_rating.max' => 'Оцінка комунікації має бути не більше 5.',

            // Location Rating
            'location_rating.required' => 'Будь ласка, оцініть розташування.',
            'location_rating.integer' => 'Оцінка розташування має бути цілим числом.',
            'location_rating.min' => 'Оцінка розташування має бути не менше 1.',
            'location_rating.max' => 'Оцінка розташування має бути не більше 5.',

            // Value Rating
            'value_rating.required' => 'Будь ласка, оцініть співвідношення ціна/якість.',
            'value_rating.integer' => 'Оцінка співвідношення ціна/якість має бути цілим числом.',
            'value_rating.min' => 'Оцінка співвідношення ціна/якість має бути не менше 1.',
            'value_rating.max' => 'Оцінка співвідношення ціна/якість має бути не більше 5.',

            // Comment
            'comment.string' => 'Коментар має бути текстом.',
            'comment.max' => 'Коментар не може перевищувати 1000 символів.',
        ];
    }
}
