<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;
use Cknow\Money\Money as LaravelMoney;

final class Pricing extends AbstractValueObject
{
    public function __construct(
        public readonly string $currency,
        public readonly LaravelMoney $pricePerPerson,
        public readonly ?LaravelMoney $privateGroupMinPrice = null,
        public readonly bool $requireMinimumPrice = true,
        public readonly bool $accessibleGuestsAllowed = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'] ?? 'USD',
            pricePerPerson: money($data['price_per_person'] ?? 0, $data['currency'] ?? 'USD'),
            privateGroupMinPrice: isset($data['private_group_min_price'])
            ? money($data['private_group_min_price'], $data['currency'] ?? 'USD')
            : null,
            requireMinimumPrice: $data['require_minimum_price'] ?? true,
            accessibleGuestsAllowed: $data['accessible_guests_allowed'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'price_per_person' => $this->pricePerPerson->getAmount(),
            'private_group_min_price' => $this->privateGroupMinPrice?->getAmount(),
            'require_minimum_price' => $this->requireMinimumPrice,
            'accessible_guests_allowed' => $this->accessibleGuestsAllowed,
        ];
    }
}
