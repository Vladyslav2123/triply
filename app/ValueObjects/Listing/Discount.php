<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class Discount extends AbstractValueObject
{
    public function __construct(
        public readonly int $weekly,
        public readonly int $monthly,
        public readonly ?int $lastMinute = null,
        public readonly ?int $earlyBird = null,
        public readonly ?array $seasonal = null,
        public readonly ?array $customPeriods = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            weekly: $data['weekly'] ?? 0,
            monthly: $data['monthly'] ?? 0,
            lastMinute: $data['last_minute'] ?? null,
            earlyBird: $data['early_bird'] ?? null,
            seasonal: $data['seasonal'] ?? null,
            customPeriods: $data['custom_periods'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'weekly' => $this->weekly,
            'monthly' => $this->monthly,
            'last_minute' => $this->lastMinute,
            'early_bird' => $this->earlyBird,
            'seasonal' => $this->seasonal,
            'custom_periods' => $this->customPeriods,
        ], fn ($value) => $value !== null);
    }

    public function hasAnyDiscount(): bool
    {
        return $this->weekly > 0 ||
            $this->monthly > 0 ||
            $this->lastMinute > 0 ||
            $this->earlyBird > 0;
    }

    public function getMaxDiscount(): int
    {
        return max(array_filter([
            $this->weekly,
            $this->monthly,
            $this->lastMinute,
            $this->earlyBird,
        ]));
    }
}
