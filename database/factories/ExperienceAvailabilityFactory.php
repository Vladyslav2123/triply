<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\ExperienceAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExperienceAvailability>
 */
class ExperienceAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $experience = Experience::query()->inRandomOrder()->first();

        if (! $experience) {
            $experience = Experience::factory()->create();
        }

        return [
            'experience_id' => $experience->id,
            'date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'is_available' => $this->faker->boolean(80), // 80% chance of being available
            'spots_available' => $this->faker->numberBetween(0, 20),
            'price_override' => $this->faker->optional(0.3)->numberBetween(1000, 10000), // 30% chance of having a price override
        ];
    }

    /**
     * Configure the factory to create availability for a specific experience.
     */
    public function forExperience(Experience $experience): static
    {
        return $this->state(function () use ($experience) {
            return [
                'experience_id' => $experience->id,
            ];
        });
    }

    /**
     * Configure the factory to create an available date.
     */
    public function available(): static
    {
        return $this->state(function () {
            return [
                'is_available' => true,
                'spots_available' => $this->faker->numberBetween(1, 20),
            ];
        });
    }

    /**
     * Configure the factory to create an unavailable date.
     */
    public function unavailable(): static
    {
        return $this->state(function () {
            return [
                'is_available' => false,
                'spots_available' => 0,
            ];
        });
    }

    /**
     * Configure the factory to create availability for a specific date.
     */
    public function forDate(\DateTime $date): static
    {
        return $this->state(function () use ($date) {
            return [
                'date' => $date,
            ];
        });
    }

    /**
     * Configure the factory to create availability with a specific number of spots.
     */
    public function withSpots(int $spots): static
    {
        return $this->state(function () use ($spots) {
            return [
                'spots_available' => $spots,
                'is_available' => $spots > 0,
            ];
        });
    }

    /**
     * Configure the factory to create availability with a price override.
     */
    public function withPriceOverride(int $price): static
    {
        return $this->state(function () use ($price) {
            return [
                'price_override' => $price,
            ];
        });
    }
}
