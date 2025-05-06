<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class AccessibilityFeature extends AbstractValueObject
{
    public function __construct(
        public readonly bool $disabledParkingSpot = false,
        public readonly bool $guestEntrance = false,
        public readonly bool $stepFreeAccess = false,
        public readonly bool $swimmingPool = false,
        public readonly bool $ceilingHoist = false,
        public readonly bool $wideHallway = false,
        public readonly bool $wideEntrance = false,
        public readonly bool $wheelchair = false,
        public readonly ?string $additionalFeatures = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            disabledParkingSpot: $data['disabled_parking_spot'] ?? false,
            guestEntrance: $data['guest_entrance'] ?? false,
            stepFreeAccess: $data['step_free_access'] ?? false,
            swimmingPool: $data['swimming_pool'] ?? false,
            ceilingHoist: $data['ceiling_hoist'] ?? false,
            wideHallway: $data['wide_hallway'] ?? false,
            wideEntrance: $data['wide_entrance'] ?? false,
            wheelchair: $data['wheelchair'] ?? false,
            additionalFeatures: $data['additional_features'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'disabled_parking_spot' => $this->disabledParkingSpot,
            'guest_entrance' => $this->guestEntrance,
            'step_free_access' => $this->stepFreeAccess,
            'swimming_pool' => $this->swimmingPool,
            'ceiling_hoist' => $this->ceilingHoist,
            'wide_hallway' => $this->wideHallway,
            'wide_entrance' => $this->wideEntrance,
            'wheelchair' => $this->wheelchair,
            'additional_features' => $this->additionalFeatures,
        ], fn ($value) => $value !== null);
    }
}
