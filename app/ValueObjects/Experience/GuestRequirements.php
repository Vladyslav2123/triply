<?php

namespace App\ValueObjects\Experience;

use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\ValueObjects\AbstractValueObject;

final class GuestRequirements extends AbstractValueObject
{
    public function __construct(
        public readonly ?int $minimum_age = null,
        public readonly bool $can_bring_children_under_2 = false,
        public readonly bool $accessibility_communication = false,
        public readonly bool $accessibility_mobility = false,
        public readonly bool $accessibility_sensory = false,
        public readonly ?PhysicalActivityLevel $physical_activity_level = null,
        public readonly ?SkillLevel $skill_level = null,
        public readonly ?string $additional_requirements = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            minimum_age: $data['minimum_age'] ?? null,
            can_bring_children_under_2: $data['can_bring_children_under_2'] ?? false,
            accessibility_communication: $data['accessibility_communication'] ?? false,
            accessibility_mobility: $data['accessibility_mobility'] ?? false,
            accessibility_sensory: $data['accessibility_sensory'] ?? false,
            physical_activity_level: isset($data['physical_activity_level'])
                ? PhysicalActivityLevel::from($data['physical_activity_level'])
                : null,
            skill_level: isset($data['skill_level'])
                ? SkillLevel::from($data['skill_level'])
                : null,
            additional_requirements: $data['additional_requirements'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'minimum_age' => $this->minimum_age,
            'can_bring_children_under_2' => $this->can_bring_children_under_2,
            'accessibility_communication' => $this->accessibility_communication,
            'accessibility_mobility' => $this->accessibility_mobility,
            'accessibility_sensory' => $this->accessibility_sensory,
            'physical_activity_level' => $this->physical_activity_level?->value,
            'skill_level' => $this->skill_level?->value,
            'additional_requirements' => $this->additional_requirements,
        ];
    }
}
