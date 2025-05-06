<?php

namespace App\Http\Requests\Experience;

use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Enums\LocationType;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateExperienceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('experience'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:32'],
            'description' => ['sometimes', 'string'],
            'location' => ['sometimes', 'array'],
            'location.country' => ['required_with:location', 'string'],
            'location.city' => ['required_with:location', 'string'],
            'location.state' => ['nullable', 'string'],
            'location.coordinates' => ['nullable', 'array'],
            'location.coordinates.latitude' => ['nullable', 'numeric'],
            'location.coordinates.longitude' => ['nullable', 'numeric'],

            'category' => ['sometimes', new Enum(ExperienceType::class)],
            'sub_category' => ['sometimes', 'string'],
            'languages' => ['sometimes', 'array'],
            'languages.*' => ['required_with:languages', new Enum(Language::class)],

            'duration' => ['sometimes', 'date'],
            'location_note' => ['nullable', 'string'],
            'location_subtype' => ['sometimes', 'string'],
            'location_type' => ['sometimes', new Enum(LocationType::class)],

            'host_bio' => ['sometimes', 'array'],
            'host_bio.about' => ['required_with:host_bio', 'string'],
            'host_bio.experience' => ['required_with:host_bio', 'string'],

            'address' => ['sometimes', 'array'],
            'address.street' => ['required_with:address', 'string'],
            'address.city' => ['required_with:address', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.country' => ['required_with:address', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'address.coordinates' => ['nullable', 'array'],
            'address.coordinates.latitude' => ['nullable', 'numeric'],
            'address.coordinates.longitude' => ['nullable', 'numeric'],

            'host_provides' => ['nullable', 'array'],
            'guest_needs' => ['nullable', 'array'],

            'guest_requirements' => ['sometimes', 'array'],
            'guest_requirements.minimum_age' => ['nullable', 'integer', 'min:0'],
            'guest_requirements.can_bring_children_under_2' => ['nullable', 'boolean'],
            'guest_requirements.physical_activity_level' => ['nullable', new Enum(PhysicalActivityLevel::class)],
            'guest_requirements.skill_level' => ['nullable', new Enum(SkillLevel::class)],
            'guest_requirements.additional_requirements' => ['nullable', 'string'],

            'name' => ['sometimes', 'string', 'max:60'],

            'grouping' => ['sometimes', 'array'],
            'grouping.general_group_max' => ['required_with:grouping', 'integer', 'min:1'],
            'grouping.private_group_max' => ['required_with:grouping', 'integer', 'min:1'],

            'starts_at' => ['sometimes', 'date'],

            'pricing' => ['sometimes', 'array'],
            'pricing.currency' => ['required_with:pricing', 'string', 'size:3'],
            'pricing.price_per_person' => ['required_with:pricing', 'integer', 'min:0'],
            'pricing.private_group_min_price' => ['nullable', 'integer', 'min:0'],
            'pricing.require_minimum_price' => ['nullable', 'boolean'],
            'pricing.accessible_guests_allowed' => ['nullable', 'boolean'],

            'discounts' => ['nullable', 'array'],

            'booking_rules' => ['sometimes', 'array'],
            'booking_rules.first_guest_deadline_hours' => ['required_with:booking_rules', 'integer', 'min:1'],
            'booking_rules.additional_guests_deadline_hours' => ['required_with:booking_rules', 'integer', 'min:1'],

            'cancellation_policy' => ['sometimes', 'array'],
            'cancellation_policy.week' => ['required_with:cancellation_policy', 'boolean'],
            'cancellation_policy.oneDay' => ['required_with:cancellation_policy', 'boolean'],

            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
