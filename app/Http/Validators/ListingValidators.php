<?php

namespace App\Http\Validators;

use App\Enums\Amenity;
use App\Enums\PropertyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ListingValidators
{
    private array $allowedSortOptions = [
        'price_asc',
        'price_desc',
        'rating_desc',
        'rating_asc',
        'created_at_desc',
        'created_at_asc',
        'title_asc',
        'title_desc',
        'reviews_count_desc',
        'reviews_count_asc',
        'popularity',
    ];

    /**
     * @throws ValidationException
     */
    public function validate(Request $request): void
    {
        $validator = Validator::make($request->query(), [
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gt:price_min',
            'type' => ['nullable', 'string', new Enum(PropertyType::class)],
            'type_subtype' => ['nullable', 'string', function ($attribute, $value, $fail) use ($request) {
                $type = $request->query('type');
                if ($type && $value) {
                    $propertyType = PropertyType::from($type);
                    if (! in_array($value, $propertyType->getSubtypes())) {
                        $fail('The selected subtype is invalid for the chosen property type.');
                    }
                }
            }],
            'location' => 'nullable|array',
            'location.*' => 'nullable|array',
            'location.address' => 'nullable|array',
            'location.address.street' => 'nullable|string|max:255',
            'location.address.city' => 'nullable|string|max:255',
            'location.address.postal_code' => 'nullable|string|max:20',
            'location.address.country' => 'nullable|string|max:255',
            'location.address.state' => 'nullable|string|max:255',
            'location.coordinates' => 'nullable|array',
            'location.coordinates.latitude' => 'nullable|numeric|between:-90,90',
            'location.coordinates.longitude' => 'nullable|numeric|between:-180,180',
            'location.radius' => 'nullable|numeric|min:0|max:1000',
            'check_in' => 'nullable|date|after:today',
            'check_out' => 'nullable|date|after:check_in',
            'guests' => 'nullable|integer|min:1',
            'amenities' => 'nullable|array',
            'amenities.*' => [
                'string',
                function ($attribute, $value, $fail) {
                    $found = false;
                    foreach (Amenity::cases() as $amenityType) {
                        if (in_array($value, $amenityType->getSubtypes())) {
                            $found = true;
                            break;
                        }
                    }
                    if (! $found) {
                        $fail('The selected amenity is invalid.');
                    }
                },
            ],
            'min_rating' => 'nullable|numeric|between:1,5',
            'sort' => ['nullable', 'string', Rule::in($this->allowedSortOptions)],
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
