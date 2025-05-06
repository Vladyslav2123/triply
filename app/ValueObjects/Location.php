<?php

namespace App\ValueObjects;

final class Location extends AbstractValueObject
{
    public function __construct(
        public readonly Address $address,
        public readonly Coordinates $coordinates
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            Address::fromArray($data['address']),
            Coordinates::fromArray($data['coordinates'])
        );
    }

    public function toArray(): array
    {
        return [
            'address' => $this->address->toArray(),
            'coordinates' => $this->coordinates->toArray(),
        ];
    }

    public function __toString(): string
    {
        $location = 'Country: '.$this->address->country.', City: '.$this->address->city;

        if ($this->address->state) {
            $location .= ', State: '.$this->address->state;
        }

        return $location;
    }
}
