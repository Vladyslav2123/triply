<?php

namespace App\ValueObjects\Experience;

use App\Enums\Drink;
use App\Enums\Equipment;
use App\Enums\Food;
use App\Enums\Ticket;
use App\Enums\Transport;
use App\ValueObjects\AbstractValueObject;

final class HostProvides extends AbstractValueObject
{
    /**
     * @param  Food[]  $food
     * @param  Drink[]  $drink
     * @param  Ticket[]  $ticket
     * @param  Transport[]  $transport
     * @param  Equipment[]  $equipment
     */
    public function __construct(
        public readonly bool $includes_vehicle = false,
        public readonly bool $includes_boat = false,
        public readonly bool $includes_motorcycle = false,
        public readonly bool $includes_air_transport = false,
        public readonly bool $includes_none = false,
        public readonly array $food = [],
        public readonly array $drink = [],
        public readonly array $ticket = [],
        public readonly array $transport = [],
        public readonly array $equipment = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            includes_vehicle: $data['includes_vehicle'] ?? false,
            includes_boat: $data['includes_boat'] ?? false,
            includes_motorcycle: $data['includes_motorcycle'] ?? false,
            includes_air_transport: $data['includes_air_transport'] ?? false,
            includes_none: $data['includes_none'] ?? false,
            food: array_map(fn ($v) => Food::tryFrom($v), $data['food'] ?? []),
            drink: array_map(fn ($v) => Drink::tryFrom($v), $data['drink'] ?? []),
            ticket: array_map(fn ($v) => Ticket::tryFrom($v), $data['ticket'] ?? []),
            transport: array_map(fn ($v) => Transport::tryFrom($v), $data['transport'] ?? []),
            equipment: array_map(fn ($v) => Equipment::tryFrom($v), $data['equipment'] ?? []),
        );
    }

    public function toArray(): array
    {
        return [
            'includes_vehicle' => $this->includes_vehicle,
            'includes_boat' => $this->includes_boat,
            'includes_motorcycle' => $this->includes_motorcycle,
            'includes_air_transport' => $this->includes_air_transport,
            'includes_none' => $this->includes_none,
            'food' => array_map(fn ($e) => $e?->value, $this->food),
            'drink' => array_map(fn ($e) => $e?->value, $this->drink),
            'ticket' => array_map(fn ($e) => $e?->value, $this->ticket),
            'transport' => array_map(fn ($e) => $e?->value, $this->transport),
            'equipment' => array_map(fn ($e) => $e?->value, $this->equipment),
        ];
    }
}
