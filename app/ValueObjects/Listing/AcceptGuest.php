<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class AcceptGuest extends AbstractValueObject
{
    public function __construct(
        public readonly bool $adults = false,
        public readonly bool $children = false,
        public readonly bool $pets = false,
        public readonly ?int $maxAdults = null,
        public readonly ?int $maxChildren = null,
        public readonly ?int $maxPets = null,
        public readonly ?string $petsRestrictions = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            adults: $data['adults'] ?? false,
            children: $data['children'] ?? false,
            pets: $data['pets'] ?? false,
            maxAdults: $data['max_adults'] ?? null,
            maxChildren: $data['max_children'] ?? null,
            maxPets: $data['max_pets'] ?? null,
            petsRestrictions: $data['pets_restrictions'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'adults' => $this->adults,
            'children' => $this->children,
            'pets' => $this->pets,
            'max_adults' => $this->maxAdults,
            'max_children' => $this->maxChildren,
            'max_pets' => $this->maxPets,
            'pets_restrictions' => $this->petsRestrictions,
        ], fn ($value) => $value !== null);
    }
}
