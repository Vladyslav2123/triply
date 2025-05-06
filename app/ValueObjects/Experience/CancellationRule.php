<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class CancellationRule extends AbstractValueObject
{
    public function __construct(
        public readonly int $refundableUntilHours,
        public readonly ?string $additionalCondition = null,
        public readonly ?string $note = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            refundableUntilHours: $data['refundable_until_hours'] ?? 0,
            additionalCondition: $data['additional_condition'] ?? null,
            note: $data['note'] ?? null,
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return array_filter([
            'refundable_until_hours' => $this->refundableUntilHours,
            'additional_condition' => $this->additionalCondition,
            'note' => $this->note,
        ]);
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
