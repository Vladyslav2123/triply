<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;
use Carbon\Carbon;

final class AvailabilitySetting extends AbstractValueObject
{
    public function __construct(
        public readonly int $minStay = 1,
        public readonly int $maxStay = 364,
        public readonly ?Carbon $availableFrom = null,
        public readonly ?Carbon $availableTo = null,
        public readonly array $blockedDates = [],
        public readonly bool $instantBooking = false,
        public readonly int $advanceBookingDays = 0,
        public readonly ?array $seasonalSettings = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            minStay: $data['min_stay'] ?? 1,
            maxStay: $data['max_stay'] ?? 364,
            availableFrom: isset($data['available_from']) ? Carbon::parse($data['available_from']) : null,
            availableTo: isset($data['available_to']) ? Carbon::parse($data['available_to']) : null,
            blockedDates: $data['blocked_dates'] ?? [],
            instantBooking: $data['instant_booking'] ?? false,
            advanceBookingDays: $data['advance_booking_days'] ?? 0,
            seasonalSettings: $data['seasonal_settings'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'min_stay' => $this->minStay,
            'max_stay' => $this->maxStay,
            'available_from' => $this->availableFrom?->toDateString(),
            'available_to' => $this->availableTo?->toDateString(),
            'blocked_dates' => $this->blockedDates,
            'instant_booking' => $this->instantBooking,
            'advance_booking_days' => $this->advanceBookingDays,
            'seasonal_settings' => $this->seasonalSettings,
        ], fn ($value) => $value !== null);
    }

    public function isAvailable(Carbon $date): bool
    {
        if ($this->availableFrom && $date->lt($this->availableFrom)) {
            return false;
        }

        if ($this->availableTo && $date->gt($this->availableTo)) {
            return false;
        }

        return ! in_array($date->toDateString(), $this->blockedDates);
    }
}
