<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class RoomRule extends AbstractValueObject
{
    public function __construct(
        public readonly int $floorsCount,
        public readonly int $floorListing,
        public readonly int $yearBuilt,
        public readonly float $propertySize,
        public readonly ?bool $elevator = null,
        public readonly ?float $ceilingHeight = null,
        public readonly ?array $roomDimensions = null,
        public readonly ?string $buildingType = null,
        public readonly ?array $renovationHistory = null,
        public readonly bool $furnished = true
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            floorsCount: $data['floors_count'] ?? 0,
            floorListing: $data['floor_listing'] ?? 0,
            yearBuilt: $data['year_built'] ?? 0,
            propertySize: $data['property_size'] ?? 0.0,
            elevator: $data['elevator'] ?? null,
            ceilingHeight: $data['ceiling_height'] ?? null,
            roomDimensions: $data['room_dimensions'] ?? null,
            buildingType: $data['building_type'] ?? null,
            renovationHistory: $data['renovation_history'] ?? null,
            furnished: $data['furnished'] ?? true
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'floors_count' => $this->floorsCount,
            'floor_listing' => $this->floorListing,
            'year_built' => $this->yearBuilt,
            'property_size' => $this->propertySize,
            'elevator' => $this->elevator,
            'ceiling_height' => $this->ceilingHeight,
            'room_dimensions' => $this->roomDimensions,
            'building_type' => $this->buildingType,
            'renovation_history' => $this->renovationHistory,
            'furnished' => $this->furnished,
        ], fn ($value) => $value !== null);
    }

    public function getAge(): int
    {
        return date('Y') - $this->yearBuilt;
    }

    public function needsElevator(): bool
    {
        return $this->floorListing > 4 && ! $this->elevator;
    }

    public function isGroundFloor(): bool
    {
        return $this->floorListing === 1;
    }

    public function isTopFloor(): bool
    {
        return $this->floorListing === $this->floorsCount;
    }
}
