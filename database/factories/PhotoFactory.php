<?php

namespace Database\Factories;

use App\Constants\PhotoConstants;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = User::query()->inRandomOrder()->first() ?? User::factory()->create();
        $profile = $user->getOrCreateProfile();

        return [
            'url' => PhotoConstants::DIRECTORY_PHOTOS.'/'.$this->faker->uuid.'.jpg',
            'disk' => 's3',
            'directory' => PhotoConstants::DIRECTORY_PHOTOS,
            'size' => $this->faker->numberBetween(100000, 5000000),
            'original_filename' => $this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'width' => $this->faker->numberBetween(800, 3000),
            'height' => $this->faker->numberBetween(600, 2000),
            'uploaded_at' => now(),
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ];
    }

    /**
     * Configure the factory to create a photo for a profile.
     */
    public function forProfile(?Profile $profile = null): static
    {
        $profile ??= User::factory()->create()->getOrCreateProfile();

        return $this->forPhotoable($profile)
            ->state([
                'directory' => PhotoConstants::DIRECTORY_USERS,
                'url' => PhotoConstants::DIRECTORY_USERS.'/'.$this->faker->uuid.'.jpg',
            ]);
    }

    /**
     * Configure the factory to create a photo for a specific model.
     */
    public function forPhotoable(Model $model): static
    {
        return $this->state(function () use ($model) {
            return [
                'photoable_type' => $model::class,
                'photoable_id' => $model->id,
            ];
        });
    }

    /**
     * Configure the factory to create a photo for a listing.
     */
    public function forListing(?Listing $listing = null): static
    {
        $listing ??= Listing::factory()->create();

        return $this->forPhotoable($listing)
            ->state([
                'directory' => PhotoConstants::DIRECTORY_LISTINGS,
                'url' => PhotoConstants::DIRECTORY_LISTINGS.'/'.$this->faker->uuid.'.jpg',
            ]);
    }

    /**
     * Configure the factory to create a photo for an experience.
     */
    public function forExperience(?Experience $experience = null): static
    {
        $experience ??= Experience::factory()->create();

        return $this->forPhotoable($experience)
            ->state([
                'directory' => PhotoConstants::DIRECTORY_EXPERIENCES,
                'url' => PhotoConstants::DIRECTORY_EXPERIENCES.'/'.$this->faker->uuid.'.jpg',
            ]);
    }
}
