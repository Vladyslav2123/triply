<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class CancellationPolicy extends AbstractValueObject
{
    public function __construct(
        public readonly bool $week = true,
        public readonly bool $oneDay = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            week: $data['week'] ?? true,
            oneDay: $data['oneDay'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'week' => $this->week,
            'oneDay' => $this->oneDay,
        ];
    }
}
