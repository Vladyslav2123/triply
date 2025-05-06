<?php

namespace App\ValueObjects;

final class Coordinates extends AbstractValueObject
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['latitude'],
            $data['longitude']
        );
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
