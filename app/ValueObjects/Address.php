<?php

namespace App\ValueObjects;

final class Address extends AbstractValueObject
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $postalCode,
        public readonly string $country,
        public readonly ?string $state = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['street'],
            $data['city'],
            $data['postal_code'],
            $data['country'],
            $data['state'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'street' => $this->street,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $this->country,
            'state' => $this->state,
        ], fn ($value) => $value !== null);
    }
}
