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

class StoreListingRequest extends FormRequest
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
            'title' => 'required|string|max:32',
            'is_published' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',

            // Description
            'description' => 'required|array',
            'description.listing_description' => 'required|string|max:1024',
            'description.your_property' => 'required|string|max:1024',
            'description.guest_access' => 'required|string|max:1024',
            'description.interaction_with_guests' => 'required|string|max:1024',
            'description.other_details' => 'required|string|max:1024',

            // Price
            'price_per_night' => 'required|array',
            'price_per_night.amount' => 'required|string|numeric',
            'price_per_night.currency' => 'required|string|size:3',
            'price_per_night.formatted' => 'sometimes|string',

            // Discounts
            'discounts' => 'required|array',
            'discounts.weekly' => 'required|integer|min:0|max:100',
            'discounts.monthly' => 'required|integer|min:0|max:100',

            // Accept Guests
            'accept_guests' => 'required|array',
            'accept_guests.adults' => 'required|boolean',
            'accept_guests.children' => 'required|boolean',
            'accept_guests.pets' => 'required|boolean',

            // Room Rules
            'rooms_rules' => 'required|array',
            'rooms_rules.floors_count' => 'required|integer|min:1',
            'rooms_rules.floor_listing' => 'required|integer|min:1',
            'rooms_rules.year_built' => 'required|integer|min:1800|max:'.date('Y'),
            'rooms_rules.property_size' => 'required|numeric|min:1',

            // Property Type
            'type' => ['required', 'string', Rule::in(PropertyType::all())],
            'subtype' => ['required', 'string', function ($attribute, $value, $fail) {
                $type = $this->input('type');
                if ($type && $value) {
                    $propertyType = PropertyType::tryFrom($type);
                    if ($propertyType && ! in_array($value, $propertyType->getSubtypes())) {
                        $fail("The selected subtype is not valid for the property type '{$type}'.");
                    }
                }
            }],
            'listing_type' => ['required', 'string', Rule::in(ListingType::all())],
            'advance_notice_type' => ['required', 'string', Rule::in(NoticeType::all())],

            // Amenities
            'amenities' => ['required', 'array', new ValidAmenities],

            // Accessibility Features
            'accessibility_features' => 'required|array',
            'accessibility_features.disabled_parking_spot' => 'required|boolean',
            'accessibility_features.guest_entrance' => 'required|boolean',
            'accessibility_features.step_free_access' => 'required|boolean',
            'accessibility_features.swimming_pool' => 'required|boolean',
            'accessibility_features.ceiling_hoist' => 'required|boolean',

            // Availability Settings
            'availability_settings' => 'required|array',
            'availability_settings.min_stay' => 'required|integer|min:1|max:364',
            'availability_settings.max_stay' => 'required|integer|min:1|max:364',

            // Location
            'location' => 'required|array',
            'location.address' => 'required|array',
            'location.address.street' => 'required|string|max:255',
            'location.address.city' => 'required|string|max:255',
            'location.address.postal_code' => 'required|string|max:20',
            'location.address.country' => 'required|string|max:255',
            'location.coordinates' => 'required|array',
            'location.coordinates.latitude' => 'required|numeric|between:-90,90',
            'location.coordinates.longitude' => 'required|numeric|between:-180,180',

            // House Rules
            'house_rules' => 'required|array',
            'house_rules.pets_allowed' => 'required|boolean',
            'house_rules.events_allowed' => 'required|boolean',
            'house_rules.smoking_allowed' => 'required|boolean',
            'house_rules.quiet_hours' => 'required|boolean',
            'house_rules.commercial_photography_allowed' => 'required|boolean',
            'house_rules.number_of_guests' => 'required|integer|min:1',
            'house_rules.additional_rules' => 'required|string|max:1024',

            // Guest Safety
            'guest_safety' => 'required|array',
            'guest_safety.smoke_detector' => 'required|boolean',
            'guest_safety.fire_extinguisher' => 'required|boolean',
            'guest_safety.security_camera' => 'required|boolean',

            // Host
            'host_id' => 'required|string|exists:users,id',
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
        $messages = [
            'title.required' => 'Назва оголошення обов\'язкова',
            'title.string' => 'Назва оголошення має бути текстом',
            'title.max' => 'Назва оголошення не може перевищувати :max символів',
            'is_published.boolean' => 'Статус публікації має бути логічним значенням',
            'is_featured.boolean' => 'Статус рекомендованого має бути логічним значенням',

            // Description
            'description.array' => 'Опис має бути масивом даних',
            'description.required' => 'Опис обов\'язковий',
            'description.listing_description.required' => 'Опис оголошення обов\'язковий',
            'description.listing_description.string' => 'Опис оголошення має бути текстом',
            'description.listing_description.max' => 'Опис оголошення не може перевищувати :max символів',
            'description.your_property.required' => 'Опис вашої нерухомості обов\'язковий',
            'description.your_property.string' => 'Опис вашої нерухомості має бути текстом',
            'description.your_property.max' => 'Опис вашої нерухомості не може перевищувати :max символів',
            'description.guest_access.required' => 'Опис доступу для гостей обов\'язковий',
            'description.guest_access.string' => 'Опис доступу для гостей має бути текстом',
            'description.guest_access.max' => 'Опис доступу для гостей не може перевищувати :max символів',
            'description.interaction_with_guests.required' => 'Опис взаємодії з гостями обов\'язковий',
            'description.interaction_with_guests.string' => 'Опис взаємодії з гостями має бути текстом',
            'description.interaction_with_guests.max' => 'Опис взаємодії з гостями не може перевищувати :max символів',
            'description.other_details.required' => 'Інші деталі обов\'язкові',
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
            'discounts.required' => 'Знижки обов\'язкові',
            'discounts.array' => 'Знижки мають бути масивом даних',
            'discounts.weekly.required' => 'Тижнева знижка обов\'язкова',
            'discounts.weekly.integer' => 'Тижнева знижка має бути цілим числом',
            'discounts.weekly.min' => 'Тижнева знижка не може бути менше :min',
            'discounts.weekly.max' => 'Тижнева знижка не може бути більше :max',
            'discounts.monthly.required' => 'Місячна знижка обов\'язкова',
            'discounts.monthly.integer' => 'Місячна знижка має бути цілим числом',
            'discounts.monthly.min' => 'Місячна знижка не може бути менше :min',
            'discounts.monthly.max' => 'Місячна знижка не може бути більше :max',

            // Accept Guests
            'accept_guests.required' => 'Інформація про прийом гостей обов\'язкова',
            'accept_guests.array' => 'Інформація про прийом гостей має бути масивом даних',
            'accept_guests.adults.required' => 'Інформація про прийом дорослих обов\'язкова',
            'accept_guests.adults.boolean' => 'Прийом дорослих має бути логічним значенням',
            'accept_guests.children.required' => 'Інформація про прийом дітей обов\'язкова',
            'accept_guests.children.boolean' => 'Прийом дітей має бути логічним значенням',
            'accept_guests.pets.required' => 'Інформація про прийом тварин обов\'язкова',
            'accept_guests.pets.boolean' => 'Прийом тварин має бути логічним значенням',

            // Room Rules
            'rooms_rules.required' => 'Правила кімнат обов\'язкові',
            'rooms_rules.array' => 'Правила кімнат мають бути масивом даних',
            'rooms_rules.floors_count.required' => 'Кількість поверхів обов\'язкова',
            'rooms_rules.floors_count.integer' => 'Кількість поверхів має бути цілим числом',
            'rooms_rules.floors_count.min' => 'Кількість поверхів не може бути менше :min',
            'rooms_rules.floor_listing.required' => 'Поверх розміщення обов\'язковий',
            'rooms_rules.floor_listing.integer' => 'Поверх розміщення має бути цілим числом',
            'rooms_rules.floor_listing.min' => 'Поверх розміщення не може бути менше :min',
            'rooms_rules.year_built.required' => 'Рік побудови обов\'язковий',
            'rooms_rules.year_built.integer' => 'Рік побудови має бути цілим числом',
            'rooms_rules.year_built.min' => 'Рік побудови не може бути менше :min',
            'rooms_rules.year_built.max' => 'Рік побудови не може бути більше :max',
            'rooms_rules.property_size.required' => 'Розмір нерухомості обов\'язковий',
            'rooms_rules.property_size.numeric' => 'Розмір нерухомості має бути числом',
            'rooms_rules.property_size.min' => 'Розмір нерухомості не може бути менше :min',

            // Property Type
            'type.required' => 'Тип нерухомості обов\'язковий',
            'type.string' => 'Тип нерухомості має бути текстом',
            'type.in' => 'Вибраний тип нерухомості недійсний',
            'subtype.required' => 'Підтип нерухомості обов\'язковий',
            'subtype.string' => 'Підтип нерухомості має бути текстом',
            'listing_type.required' => 'Тип оголошення обов\'язковий',
            'listing_type.string' => 'Тип оголошення має бути текстом',
            'listing_type.in' => 'Вибраний тип оголошення недійсний',
            'advance_notice_type.required' => 'Тип попереднього повідомлення обов\'язковий',
            'advance_notice_type.string' => 'Тип попереднього повідомлення має бути текстом',
            'advance_notice_type.in' => 'Вибраний тип попереднього повідомлення недійсний',

            // Amenities
            'amenities.required' => 'Зручності обов\'язкові',
            'amenities.array' => 'Зручності мають бути масивом даних',

            // Accessibility Features
            'accessibility_features.required' => 'Інформація про доступність обов\'язкова',
            'accessibility_features.array' => 'Інформація про доступність має бути масивом даних',
            'accessibility_features.disabled_parking_spot.required' => 'Інформація про паркування для людей з інвалідністю обов\'язкова',
            'accessibility_features.disabled_parking_spot.boolean' => 'Паркування для людей з інвалідністю має бути логічним значенням',
            'accessibility_features.guest_entrance.required' => 'Інформація про вхід для гостей обов\'язкова',
            'accessibility_features.guest_entrance.boolean' => 'Вхід для гостей має бути логічним значенням',
            'accessibility_features.step_free_access.required' => 'Інформація про безсходинковий доступ обов\'язкова',
            'accessibility_features.step_free_access.boolean' => 'Безсходинковий доступ має бути логічним значенням',
            'accessibility_features.swimming_pool.required' => 'Інформація про басейн обов\'язкова',
            'accessibility_features.swimming_pool.boolean' => 'Басейн має бути логічним значенням',
            'accessibility_features.ceiling_hoist.required' => 'Інформація про стельовий підйомник обов\'язкова',
            'accessibility_features.ceiling_hoist.boolean' => 'Стельовий підйомник має бути логічним значенням',

            // Availability Settings
            'availability_settings.required' => 'Налаштування доступності обов\'язкові',
            'availability_settings.array' => 'Налаштування доступності мають бути масивом даних',
            'availability_settings.min_stay.required' => 'Мінімальний термін перебування обов\'язковий',
            'availability_settings.min_stay.integer' => 'Мінімальний термін перебування має бути цілим числом',
            'availability_settings.min_stay.min' => 'Мінімальний термін перебування не може бути менше :min дня',
            'availability_settings.min_stay.max' => 'Мінімальний термін перебування не може бути більше :max днів',
            'availability_settings.max_stay.required' => 'Максимальний термін перебування обов\'язковий',
            'availability_settings.max_stay.integer' => 'Максимальний термін перебування має бути цілим числом',
            'availability_settings.max_stay.min' => 'Максимальний термін перебування не може бути менше :min дня',
            'availability_settings.max_stay.max' => 'Максимальний термін перебування не може бути більше :max днів',

            // Location
            'location.required' => 'Місцезнаходження обов\'язкове',
            'location.array' => 'Місцезнаходження має бути масивом даних',
            'location.address.required' => 'Адреса обов\'язкова',
            'location.address.array' => 'Адреса має бути масивом даних',
            'location.address.street.required' => 'Вулиця обов\'язкова',
            'location.address.street.string' => 'Вулиця має бути текстом',
            'location.address.street.max' => 'Вулиця не може перевищувати :max символів',
            'location.address.city.required' => 'Місто обов\'язкове',
            'location.address.city.string' => 'Місто має бути текстом',
            'location.address.city.max' => 'Місто не може перевищувати :max символів',
            'location.address.postal_code.required' => 'Поштовий індекс обов\'язковий',
            'location.address.postal_code.string' => 'Поштовий індекс має бути текстом',
            'location.address.postal_code.max' => 'Поштовий індекс не може перевищувати :max символів',
            'location.address.country.required' => 'Країна обов\'язкова',
            'location.address.country.string' => 'Країна має бути текстом',
            'location.address.country.max' => 'Країна не може перевищувати :max символів',
            'location.coordinates.required' => 'Координати обов\'язкові',
            'location.coordinates.array' => 'Координати мають бути масивом даних',
            'location.coordinates.latitude.required' => 'Широта обов\'язкова',
            'location.coordinates.latitude.numeric' => 'Широта має бути числом',
            'location.coordinates.latitude.between' => 'Широта має бути між :min та :max',
            'location.coordinates.longitude.required' => 'Довгота обов\'язкова',
            'location.coordinates.longitude.numeric' => 'Довгота має бути числом',
            'location.coordinates.longitude.between' => 'Довгота має бути між :min та :max',

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

        // Додати власні повідомлення для кожного типу та підтипу зручностей
        foreach (Amenity::cases() as $case) {
            $messages['amenities.'.$case->value.'.array'] = 'Зручності типу "'.$case->value.'" мають бути масивом даних';
            $messages['amenities.'.$case->value.'.*.in'] = 'Вибрана зручність типу "'.$case->value.'" недійсна';
        }

        return $messages;
    }
}
