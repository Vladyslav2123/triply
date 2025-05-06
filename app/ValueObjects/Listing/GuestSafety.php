<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class GuestSafety extends AbstractValueObject
{
    public function __construct(
        public readonly bool $smokeDetector,
        public readonly bool $fireExtinguisher,
        public readonly bool $securityCamera,
        public readonly bool $firstAidKit = false,
        public readonly bool $carbonMonoxideDetector = false,
        public readonly bool $emergencyExit = false,
        public readonly ?string $emergencyContacts = null,
        public readonly ?string $safetyInstructions = null,
        public readonly array $additionalSafetyFeatures = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            smokeDetector: $data['smoke_detector'] ?? false,
            fireExtinguisher: $data['fire_extinguisher'] ?? false,
            securityCamera: $data['security_camera'] ?? false,
            firstAidKit: $data['first_aid_kit'] ?? false,
            carbonMonoxideDetector: $data['carbon_monoxide_detector'] ?? false,
            emergencyExit: $data['emergency_exit'] ?? false,
            emergencyContacts: $data['emergency_contacts'] ?? null,
            safetyInstructions: $data['safety_instructions'] ?? null,
            additionalSafetyFeatures: $data['additional_safety_features'] ?? []
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'smoke_detector' => $this->smokeDetector,
            'fire_extinguisher' => $this->fireExtinguisher,
            'security_camera' => $this->securityCamera,
            'first_aid_kit' => $this->firstAidKit,
            'carbon_monoxide_detector' => $this->carbonMonoxideDetector,
            'emergency_exit' => $this->emergencyExit,
            'emergency_contacts' => $this->emergencyContacts,
            'safety_instructions' => $this->safetyInstructions,
            'additional_safety_features' => $this->additionalSafetyFeatures,
        ], fn ($value) => $value !== null);
    }

    public function hasBasicSafety(): bool
    {
        return $this->smokeDetector && $this->fireExtinguisher;
    }

    public function getSafetyScore(): int
    {
        return array_sum([
            $this->smokeDetector,
            $this->fireExtinguisher,
            $this->securityCamera,
            $this->firstAidKit,
            $this->carbonMonoxideDetector,
            $this->emergencyExit,
        ]);
    }
}
