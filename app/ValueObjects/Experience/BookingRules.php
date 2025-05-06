<?php

namespace App\ValueObjects\Experience;

use App\Enums\BookingDeadline;
use App\ValueObjects\AbstractValueObject;

final class BookingRules extends AbstractValueObject
{
    public function __construct(
        public readonly BookingDeadline $firstGuestDeadline,
        public readonly BookingDeadline $additionalGuestsDeadline,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstGuestDeadline: BookingDeadline::fromHours($data['first_guest_deadline_hours'] ?? 24),
            additionalGuestsDeadline: BookingDeadline::fromHours($data['additional_guests_deadline_hours'] ?? 12),
        );
    }

    public function toArray(): array
    {
        return [
            'first_guest_deadline_hours' => $this->firstGuestDeadline->toHours(),
            'additional_guests_deadline_hours' => $this->additionalGuestsDeadline->toHours(),
        ];
    }
}
