<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class GroupSize extends AbstractValueObject
{
    public function __construct(
        public readonly int $generalGroupMax,
        public readonly int $privateGroupMax,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            generalGroupMax: $data['general_group_max'] ?? 10,
            privateGroupMax: $data['private_group_max'] ?? 30,
        );
    }

    public function toArray(): array
    {
        return [
            'general_group_max' => $this->generalGroupMax,
            'private_group_max' => $this->privateGroupMax,
        ];
    }
}
