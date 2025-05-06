<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\ExperienceAvailability;
use App\Models\Listing;
use App\Models\ListingAvailability;
use App\Models\Reservation;
use App\Models\User;
use DateMalformedStringException;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws DateMalformedStringException
     */
    public function definition(): array
    {
        $check_in = $this->faker->dateTimeBetween(now(), now()->addDays(18));
        $check_out = $this->faker->dateTimeBetween($check_in, (clone $check_in)->modify('+10 day'));

        return [
            'guest_id' => Str::ulid(),
            'check_in' => $check_in,
            'check_out' => $check_out,
            'status' => $this->faker->randomElement(ReservationStatus::cases())->value,
            'total_price' => $this->faker->numberBetween(1000, 10000),
        ];
    }

    /**
     * Set a fake guest_id without creating a user.
     */
    public function withFakeGuestId(): static
    {
        return $this->state(function () {
            return [
                'guest_id' => Str::ulid(),
            ];
        });
    }

    /**
     * Create a completed reservation.
     */
    public function completed(): static
    {
        return $this->state(function () {
            return [
                'status' => ReservationStatus::COMPLETED->value,
            ];
        });
    }

    public function forReservable(Model $reservable): static
    {
        return $this->afterMaking(function (Reservation $reservation) use ($reservable) {
            $reservation->reservationable()->associate($reservable);

            $hostId = $reservable->host_id ?? null;

            $user = User::query()
                ->when($hostId, fn ($q) => $q->where('id', '!=', $hostId))
                ->inRandomOrder()
                ->first();

            $reservation->guest_id = $user?->id;

            if ($reservable instanceof Listing) {
                $maxTries = 30;
                $tries = 0;

                do {
                    $check_in = $this->faker->dateTimeBetween(now(), now()->addDays(180));
                    $check_out = $this->faker->dateTimeBetween($check_in, (clone $check_in)->modify('+10 day'));
                    $tries++;
                } while (
                    ! $this->checkAvailability($reservable, $check_in, $check_out)
                    && $tries < $maxTries
                );

                if ($tries === $maxTries) {
                    throw new RuntimeException("Could not find available dates for listing ID {$reservable->id}");
                }

                $reservation->check_in = $check_in;
                $reservation->check_out = $check_out;

                $this->updateListingAvailability($reservable, $check_in, $check_out);
                $reservation->total_price = $this->getTotalPrice($reservable, $check_in, $check_out);
            }

            if ($reservable instanceof Experience) {
                $maxTries = 30;
                $tries = 0;

                do {
                    $check_in = $this->faker->dateTimeBetween(now(), now()->addDays(180));
                    $tries++;
                } while (
                    ! $this->checkExperienceAvailability($reservable, $check_in)
                    && $tries < $maxTries
                );

                if ($tries === $maxTries) {
                    throw new RuntimeException("Could not find available date for experience ID {$reservable->id}");
                }

                $reservation->check_in = $check_in;
                $reservation->check_out = (clone $check_in)->modify('+2 hours');

                $this->updateExperienceAvailability($reservable, $check_in);

                $reservation->total_price = $this->getPriceExperience($reservable);
            }
        });
    }

    /**
     * @throws DateMalformedStringException
     */
    private function checkAvailability(Listing $listing, DateTime $in, DateTime $out): bool
    {
        $dates = collect();
        for ($date = clone $in; $date <= $out; $date->modify('+1 day')) {
            $dates->push($date->format('Y-m-d'));
        }

        return ! ListingAvailability::query()
            ->where('listing_id', $listing->id)
            ->whereIn('date', $dates)
            ->where('is_available', false)
            ->exists();
    }

    /**
     * @throws DateMalformedStringException
     */
    private function updateListingAvailability(Listing $listing, DateTime $in, DateTime $out): void
    {
        for ($date = clone $in; $date <= $out; $date->modify('+1 day')) {
            ListingAvailability::updateOrCreate(
                ['listing_id' => $listing->id, 'date' => $date->format('Y-m-d')],
                ['is_available' => false]
            );
        }
    }

    private function getTotalPrice(Listing $listing, DateTime $in, DateTime $out): int
    {
        $days = (int) $out->diff($in)->days ?: 1;

        return $listing->price_per_night->multiply($days)->getAmount();
    }

    private function checkExperienceAvailability(Experience $experience, DateTime $date): bool
    {
        return ! ExperienceAvailability::query()
            ->where('experience_id', $experience->id)
            ->where('date', $date->format('Y-m-d H:i:s'))
            ->where('is_available', false)
            ->exists();
    }

    private function updateExperienceAvailability(Experience $experience, DateTime $date): void
    {
        ExperienceAvailability::updateOrCreate(
            [
                'experience_id' => $experience->id,
                'date' => $date->format('Y-m-d H:i:s'),
            ],
            [
                'is_available' => false,
            ]
        );
    }

    private function getPriceExperience(Experience $experience): int
    {
        return $experience->pricing->pricePerPerson->multiply($experience->grouping->generalGroupMax)->getAmount();
    }
}
