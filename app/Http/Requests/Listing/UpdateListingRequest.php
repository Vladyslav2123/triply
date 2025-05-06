<?php

namespace App\Http\Requests\Listing;

use App\Enums\Amenity;
use App\Enums\ListingType;
use App\Enums\NoticeType;
use App\Enums\PropertyType;
use App\Rules\ValidAmenities;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('listing'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:32',
            'is_published' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',

            // Description
            'description' => 'sometimes|array',
            'description.listing_description' => 'sometimes|string|max:1024',
            'description.your_property' => 'sometimes|string|max:1024',
            'description.guest_access' => 'sometimes|string|max:1024',
            'description.interaction_with_guests' => 'sometimes|string|max:1024',
            'description.other_details' => 'sometimes|string|max:1024',

            // Price
            'price_per_night' => 'sometimes|array',
            'price_per_night.amount' => 'sometimes|string|numeric',
            'price_per_night.currency' => 'sometimes|string|size:3',
            'price_per_night.formatted' => 'sometimes|string',

            // Discounts
            'discounts' => 'sometimes|array',
            'discounts.weekly' => 'sometimes|integer|min:0|max:100',
            'discounts.monthly' => 'sometimes|integer|min:0|max:100',

            // Accept Guests
            'accept_guests' => 'sometimes|array',
            'accept_guests.adults' => 'sometimes|boolean',
            'accept_guests.children' => 'sometimes|boolean',
            'accept_guests.pets' => 'sometimes|boolean',

            // Room Rules
            'rooms_rules' => 'sometimes|array',
            'rooms_rules.floors_count' => 'sometimes|integer|min:1',
            'rooms_rules.floor_listing' => 'sometimes|integer|min:1',
            'rooms_rules.year_built' => 'sometimes|integer|min:1800|max:'.date('Y'),
            'rooms_rules.property_size' => 'sometimes|numeric|min:1',

            // Property Type
            'type' => ['sometimes', 'string', Rule::in(PropertyType::all())],
            'subtype' => ['sometimes', 'string', function ($attribute, $value, $fail) {
                $type = $this->input('type');
                if ($type && $value) {
                    $propertyType = PropertyType::tryFrom($type);
                    if ($propertyType && ! in_array($value, $propertyType->getSubtypes())) {
                        $fail("The selected subtype is not valid for the property type '{$type}'.");
                    }
                }
            }],
            'listing_type' => ['sometimes', 'string', Rule::in(ListingType::all())],
            'advance_notice_type' => ['sometimes', 'string', Rule::in(NoticeType::all())],

            // Amenities
            'amenities' => ['sometimes', 'array', new ValidAmenities],

            // Accessibility Features
            'accessibility_features' => 'sometimes|array',
            'accessibility_features.disabled_parking_spot' => 'sometimes|boolean',
            'accessibility_features.guest_entrance' => 'sometimes|boolean',
            'accessibility_features.step_free_access' => 'sometimes|boolean',
            'accessibility_features.swimming_pool' => 'sometimes|boolean',
            'accessibility_features.ceiling_hoist' => 'sometimes|boolean',

            // Availability Settings
            'availability_settings' => 'sometimes|array',
            'availability_settings.min_stay' => 'sometimes|integer|min:1|max:364',
            'availability_settings.max_stay' => 'sometimes|integer|min:1|max:364',

            // Location
            'location' => 'sometimes|array',
            'location.address' => 'sometimes|array',
            'location.address.street' => 'sometimes|string|max:255',
            'location.address.city' => 'sometimes|string|max:255',
            'location.address.postal_code' => 'sometimes|string|max:20',
            'location.address.country' => 'sometimes|string|max:255',
            'location.coordinates' => 'sometimes|array',
            'location.coordinates.latitude' => 'sometimes|numeric|between:-90,90',
            'location.coordinates.longitude' => 'sometimes|numeric|between:-180,180',

            // House Rules
            'house_rules' => 'sometimes|array',
            'house_rules.pets_allowed' => 'sometimes|boolean',
            'house_rules.events_allowed' => 'sometimes|boolean',
            'house_rules.smoking_allowed' => 'sometimes|boolean',
            'house_rules.quiet_hours' => 'sometimes|boolean',
            'house_rules.commercial_photography_allowed' => 'sometimes|boolean',
            'house_rules.number_of_guests' => 'sometimes|integer|min:1',
            'house_rules.additional_rules' => 'sometimes|string|max:1024',

            // Guest Safety
            'guest_safety' => 'sometimes|array',
            'guest_safety.smoke_detector' => 'sometimes|boolean',
            'guest_safety.fire_extinguisher' => 'sometimes|boolean',
            'guest_safety.security_camera' => 'sometimes|boolean',

            // Host
            'host_id' => 'sometimes|string|exists:users,id',
        ] + $this->getAmenityRules();
    }

    /**
     * Get validation rules for amenities.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function getAmenityRules(): array
    {
        $rules = [];

        // Validate amenity types
        foreach (Amenity::cases() as $case) {
            $rules['amenities.'.$case->value] = ['sometimes', 'array'];

            // Validate amenity subtypes
            $validSubtypes = $case->getSubtypes();
            $rules['amenities.'.$case->value.'.*'] = [
                'string',
                Rule::in($validSubtypes),
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Назва оголошення обов\'язкова',
            'title.string' => 'Назва оголошення має бути текстом',
            'title.max' => 'Назва оголошення не може перевищувати :max символів',
            'is_published.boolean' => 'Статус публікації має бути логічним значенням',
            'is_featured.boolean' => 'Статус рекомендованого має бути логічним значенням',

            // Description
            'description.array' => 'Опис має бути масивом даних',
            'description.listing_description.string' => 'Опис оголошення має бути текстом',
            'description.listing_description.max' => 'Опис оголошення не може перевищувати :max символів',
            'description.your_property.string' => 'Опис вашої нерухомості має бути текстом',
            'description.your_property.max' => 'Опис вашої нерухомості не може перевищувати :max символів',
            'description.guest_access.string' => 'Опис доступу для гостей має бути текстом',
            'description.guest_access.max' => 'Опис доступу для гостей не може перевищувати :max символів',
            'description.interaction_with_guests.string' => 'Опис взаємодії з гостями має бути текстом',
            'description.interaction_with_guests.max' => 'Опис взаємодії з гостями не може перевищувати :max символів',
            'description.other_details.string' => 'Інші деталі мають бути текстом',
            'description.other_details.max' => 'Інші деталі не можуть перевищувати :max символів',

            // Price
            'price_per_night.required' => 'Ціна за ніч обов\'язкова',
            'price_per_night.array' => 'Ціна за ніч має бути масивом даних',
            'price_per_night.amount.required' => 'Сума ціни обов\'язкова',
            'price_per_night.amount.numeric' => 'Сума ціни має бути числом',
            'price_per_night.currency.required' => 'Валюта ціни обов\'язкова',
            'price_per_night.currency.size' => 'Код валюти має бути :size символи',

            // Discounts
            'discounts.array' => 'Знижки мають бути масивом даних',
            'discounts.weekly.integer' => 'Тижнева знижка має бути цілим числом',
            'discounts.weekly.min' => 'Тижнева знижка не може бути менше :min',
            'discounts.weekly.max' => 'Тижнева знижка не може бути більше :max',
            'discounts.monthly.integer' => 'Місячна знижка має бути цілим числом',
            'discounts.monthly.min' => 'Місячна знижка не може бути менше :min',
            'discounts.monthly.max' => 'Місячна знижка не може бути більше :max',

            // Property Type
            'type.required' => 'Тип нерухомості обов\'язковий',
            'type.in' => 'Вибраний тип нерухомості недійсний',
            'subtype.required' => 'Підтип нерухомості обов\'язковий',
            'subtype.string' => 'Підтип нерухомості має бути текстом',
            'listing_type.required' => 'Тип оголошення обов\'язковий',
            'listing_type.in' => 'Вибраний тип оголошення недійсний',
            'advance_notice_type.required' => 'Тип попереднього повідомлення обов\'язковий',
            'advance_notice_type.in' => 'Вибраний тип попереднього повідомлення недійсний',

            // Location
            'location.array' => 'Місцезнаходження має бути масивом даних',
            'location.address.array' => 'Адреса має бути масивом даних',
            'location.address.street.string' => 'Вулиця має бути текстом',
            'location.address.street.max' => 'Вулиця не може перевищувати :max символів',
            'location.address.city.string' => 'Місто має бути текстом',
            'location.address.city.max' => 'Місто не може перевищувати :max символів',
            'location.address.postal_code.string' => 'Поштовий індекс має бути текстом',
            'location.address.postal_code.max' => 'Поштовий індекс не може перевищувати :max символів',
            'location.address.country.string' => 'Країна має бути текстом',
            'location.address.country.max' => 'Країна не може перевищувати :max символів',
            'location.coordinates.array' => 'Координати мають бути масивом даних',
            'location.coordinates.latitude.numeric' => 'Широта має бути числом',
            'location.coordinates.latitude.between' => 'Широта має бути між -90 та 90',
            'location.coordinates.longitude.numeric' => 'Довгота має бути числом',
            'location.coordinates.longitude.between' => 'Довгота має бути між -180 та 180',

            // House Rules
            'house_rules.array' => 'Правила будинку мають бути масивом даних',
            'house_rules.pets_allowed.boolean' => 'Дозвіл на утримання тварин має бути логічним значенням',
            'house_rules.events_allowed.boolean' => 'Дозвіл на проведення заходів має бути логічним значенням',
            'house_rules.smoking_allowed.boolean' => 'Дозвіл на куріння має бути логічним значенням',
            'house_rules.quiet_hours.boolean' => 'Години тиші мають бути логічним значенням',
            'house_rules.commercial_photography_allowed.boolean' => 'Дозвіл на комерційну фотозйомку має бути логічним значенням',
            'house_rules.number_of_guests.integer' => 'Кількість гостей має бути цілим числом',
            'house_rules.number_of_guests.min' => 'Кількість гостей не може бути менше :min',
            'house_rules.additional_rules.string' => 'Додаткові правила мають бути текстом',
            'house_rules.additional_rules.max' => 'Додаткові правила не можуть перевищувати :max символів',

            // Guest Safety
            'guest_safety.array' => 'Безпека гостей має бути масивом даних',
            'guest_safety.smoke_detector.boolean' => 'Наявність димового датчика має бути логічним значенням',
            'guest_safety.fire_extinguisher.boolean' => 'Наявність вогнегасника має бути логічним значенням',
            'guest_safety.security_camera.boolean' => 'Наявність камери безпеки має бути логічним значенням',

            // Host
            'host_id.required' => 'ID господаря обов\'язковий',
            'host_id.exists' => 'Вибраний господар не існує',
        ];
    }
}
