<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use RuntimeException;

/**
 * @extends Factory<Favorite>
 */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'added_at' => now(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Favorite $favorite) {
            if (! $favorite->favoriteable_type || ! $favorite->favoriteable_id) {
                throw new RuntimeException('Favoriteable type and ID must be set');
            }
        });
    }

    /**
     * Configure the favorite for a listing.
     */
    public function forListing(?Listing $listing = null): static
    {
        return $this->state(function () use ($listing) {
            $listing = $listing ?? Listing::query()->inRandomOrder()->first();

            if (! $listing) {
                $listing = Listing::factory()->create();
            }

            return [
                'favoriteable_type' => 'listing',
                'favoriteable_id' => $listing->id,
                'user_id' => User::query()
                    ->where('id', '!=', $listing->host_id)
                    ->inRandomOrder()
                    ->value('id'),
            ];
        });
    }

    /**
     * Configure the favorite for an experience.
     */
    public function forExperience(?Experience $experience = null): static
    {
        return $this->state(function () use ($experience) {
            $experience = $experience ?? Experience::query()->inRandomOrder()->first();

            if (! $experience) {
                $experience = Experience::factory()->create();
            }

            return [
                'favoriteable_type' => 'experience',
                'favoriteable_id' => $experience->id,
                'user_id' => User::query()
                    ->where('id', '!=', $experience->host_id)
                    ->inRandomOrder()
                    ->value('id'),
            ];
        });
    }
}
