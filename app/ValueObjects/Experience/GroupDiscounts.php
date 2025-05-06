<?php

namespace App\ValueObjects\Experience;

use App\ValueObjects\AbstractValueObject;

final class GroupDiscounts extends AbstractValueObject
{
    /** @return void */
    public function __construct(
        public readonly array $discounts = []
    ) {}

    public static function fromArray(array $items): self
    {
        $discounts = array_map(
            fn ($item) => GroupDiscount::fromArray($item),
            $items
        );

        return new self($discounts);
    }

    public function toArray(): array
    {
        return array_map(
            fn (GroupDiscount $discount) => $discount->toArray(),
            $this->discounts
        );
    }
}
