<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class GuestNeeds extends AbstractValueObject
{
    public function __construct(
        public readonly bool $enabled = false,
        public readonly ?array $items = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            enabled: $data['enabled'] ?? false,
            items: $data['items'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'items' => $this->items,
        ];
    }
}
