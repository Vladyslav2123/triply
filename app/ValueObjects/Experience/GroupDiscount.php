<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;
use Cknow\Money\Money;

final class GroupDiscount extends AbstractValueObject
{
    public function __construct(
        public readonly int $min,
        public readonly int $max,
        public readonly int $discount,
        public readonly string $currency,
        public readonly Money $pricePerPerson,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            min: $data['min'] ?? 2,
            max: $data['max'] ?? 6,
            discount: $data['discount'] ?? 20,
            currency: $data['currency'] ?? 'USD',
            pricePerPerson: money($data['price_per_person'] ?? 0, $data['currency'] ?? 'USD'),
        );
    }

    public function toArray(): array
    {
        return [
            'min' => $this->min,
            'max' => $this->max,
            'discount' => $this->discount,
            'currency' => $this->currency,
            'price_per_person' => $this->pricePerPerson->getAmount(),
        ];
    }
}
