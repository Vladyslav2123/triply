<?php

namespace App\ValueObjects\Listing;

use App\ValueObjects\AbstractValueObject;

final class HouseRule extends AbstractValueObject
{
    public function __construct(
        public readonly bool $petsAllowed,
        public readonly bool $eventsAllowed,
        public readonly bool $smokingAllowed,
        public readonly bool $quietHours,
        public readonly bool $commercialPhotographyAllowed,
        public readonly int $numberOfGuests,
        public readonly ?string $additionalRules = null,
        public readonly ?array $quietHoursTimes = null,
        public readonly ?int $maxPets = null,
        public readonly ?int $maxEventAttendees = null,
        public readonly bool $partiesAllowed = false,
        public readonly ?string $checkInTime = null,
        public readonly ?string $checkOutTime = null,
        public readonly array $restrictedAreas = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            petsAllowed: $data['pets_allowed'] ?? false,
            eventsAllowed: $data['events_allowed'] ?? false,
            smokingAllowed: $data['smoking_allowed'] ?? false,
            quietHours: $data['quiet_hours'] ?? false,
            commercialPhotographyAllowed: $data['commercial_photography_allowed'] ?? false,
            numberOfGuests: $data['number_of_guests'] ?? 1,
            additionalRules: $data['additional_rules'] ?? '',
            quietHoursTimes: $data['quiet_hours_times'] ?? null,
            maxPets: $data['max_pets'] ?? null,
            maxEventAttendees: $data['max_event_attendees'] ?? null,
            partiesAllowed: $data['parties_allowed'] ?? false,
            checkInTime: $data['check_in_time'] ?? null,
            checkOutTime: $data['check_out_time'] ?? null,
            restrictedAreas: $data['restricted_areas'] ?? []
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'pets_allowed' => $this->petsAllowed,
            'events_allowed' => $this->eventsAllowed,
            'smoking_allowed' => $this->smokingAllowed,
            'quiet_hours' => $this->quietHours,
            'commercial_photography_allowed' => $this->commercialPhotographyAllowed,
            'number_of_guests' => $this->numberOfGuests,
            'additional_rules' => $this->additionalRules,
            'quiet_hours_times' => $this->quietHoursTimes,
            'max_pets' => $this->maxPets,
            'max_event_attendees' => $this->maxEventAttendees,
            'parties_allowed' => $this->partiesAllowed,
            'check_in_time' => $this->checkInTime,
            'check_out_time' => $this->checkOutTime,
            'restricted_areas' => $this->restrictedAreas,
        ], fn ($value) => $value !== null);
    }

    public function isGuestCountValid(int $count): bool
    {
        return $count <= $this->numberOfGuests;
    }

    public function isPetCountValid(?int $count): bool
    {
        if (! $this->petsAllowed) {
            return false;
        }

        if ($this->maxPets === null) {
            return true;
        }

        return $count <= $this->maxPets;
    }

    public function isEventValid(int $attendees): bool
    {
        if (! $this->eventsAllowed) {
            return false;
        }

        if ($this->maxEventAttendees === null) {
            return true;
        }

        return $attendees <= $this->maxEventAttendees;
    }
}
