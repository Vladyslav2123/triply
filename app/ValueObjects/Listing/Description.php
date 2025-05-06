<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class Description extends AbstractValueObject
{
    public function __construct(
        public readonly string $listingDescription,
        public readonly string $yourProperty,
        public readonly string $guestAccess,
        public readonly string $interactionWithGuests,
        public readonly string $otherDetailsToNote,
        public readonly ?string $neighborhood = null,
        public readonly ?string $transportation = null,
        public readonly array $highlights = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            listingDescription: $data['listing_description'] ?? '',
            yourProperty: $data['your_property'] ?? '',
            guestAccess: $data['guest_access'] ?? '',
            interactionWithGuests: $data['interaction_with_guests'] ?? '',
            otherDetailsToNote: $data['other_details'] ?? '',
            neighborhood: $data['neighborhood'] ?? null,
            transportation: $data['transportation'] ?? null,
            highlights: $data['highlights'] ?? []
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'listing_description' => $this->listingDescription,
            'your_property' => $this->yourProperty,
            'guest_access' => $this->guestAccess,
            'interaction_with_guests' => $this->interactionWithGuests,
            'other_details' => $this->otherDetailsToNote,
            'neighborhood' => $this->neighborhood,
            'transportation' => $this->transportation,
            'highlights' => $this->highlights,
        ], fn ($value) => $value !== null);
    }

    public function getDescriptionLength(): int
    {
        return strlen($this->listingDescription);
    }

    public function hasAllRequiredFields(): bool
    {
        return ! empty($this->listingDescription) &&
            ! empty($this->yourProperty) &&
            ! empty($this->guestAccess);
    }
}
