<?php

namespace App\Http\Requests\Experience;

use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Enums\LocationType;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\Models\Experience;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreExperienceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Experience::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:32'],
            'description' => ['required', 'string'],
            'location' => ['required', 'array'],
            'location.country' => ['required', 'string'],
            'location.city' => ['required', 'string'],
            'location.state' => ['nullable', 'string'],
            'location.coordinates' => ['nullable', 'array'],
            'location.coordinates.latitude' => ['nullable', 'numeric'],
            'location.coordinates.longitude' => ['nullable', 'numeric'],

            'category' => ['required', new Enum(ExperienceType::class)],
            'sub_category' => ['required', 'string'],
            'languages' => ['required', 'array'],
            'languages.*' => ['required', new Enum(Language::class)],

            'duration' => ['required', 'date'],
            'location_note' => ['nullable', 'string'],
            'location_subtype' => ['required', 'string'],
            'location_type' => ['required', new Enum(LocationType::class)],

            'host_bio' => ['required', 'array'],
            'host_bio.about' => ['required', 'string'],
            'host_bio.experience' => ['required', 'string'],

            'address' => ['required', 'array'],
            'address.street' => ['required', 'string'],
            'address.city' => ['required', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.country' => ['required', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'address.coordinates' => ['nullable', 'array'],
            'address.coordinates.latitude' => ['nullable', 'numeric'],
            'address.coordinates.longitude' => ['nullable', 'numeric'],

            'host_provides' => ['nullable', 'array'],
            'guest_needs' => ['nullable', 'array'],

            'guest_requirements' => ['required', 'array'],
            'guest_requirements.minimum_age' => ['nullable', 'integer', 'min:0'],
            'guest_requirements.can_bring_children_under_2' => ['nullable', 'boolean'],
            'guest_requirements.physical_activity_level' => ['nullable', new Enum(PhysicalActivityLevel::class)],
            'guest_requirements.skill_level' => ['nullable', new Enum(SkillLevel::class)],
            'guest_requirements.additional_requirements' => ['nullable', 'string'],

            'name' => ['required', 'string', 'max:60'],

            'grouping' => ['required', 'array'],
            'grouping.general_group_max' => ['required', 'integer', 'min:1'],
            'grouping.private_group_max' => ['required', 'integer', 'min:1'],

            'starts_at' => ['required', 'date'],

            'pricing' => ['required', 'array'],
            'pricing.currency' => ['required', 'string', 'size:3'],
            'pricing.price_per_person' => ['required', 'integer', 'min:0'],
            'pricing.private_group_min_price' => ['nullable', 'integer', 'min:0'],
            'pricing.require_minimum_price' => ['nullable', 'boolean'],
            'pricing.accessible_guests_allowed' => ['nullable', 'boolean'],

            'discounts' => ['nullable', 'array'],

            'booking_rules' => ['required', 'array'],
            'booking_rules.first_guest_deadline_hours' => ['required', 'integer', 'min:1'],
            'booking_rules.additional_guests_deadline_hours' => ['required', 'integer', 'min:1'],

            'cancellation_policy' => ['required', 'array'],
            'cancellation_policy.week' => ['required', 'boolean'],
            'cancellation_policy.oneDay' => ['required', 'boolean'],
        ];
    }
}
