<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedListingFavorites();
        $this->seedExperienceFavorites();
    }

    /**
     * Seed favorites for listings.
     */
    private function seedListingFavorites(): void
    {
        $listings = Listing::query()->inRandomOrder()->limit(3)->get();

        if ($listings->isEmpty()) {
            Log::warning('No listings found for seeding favorites');

            return;
        }

        foreach ($listings as $listing) {
            $users = User::query()
                ->where('id', '!=', $listing->host_id)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            if ($users->isEmpty()) {
                Log::warning("No eligible users found for listing {$listing->id}");

                continue;
            }

            foreach ($users as $user) {
                Favorite::factory()->create([
                    'user_id' => $user->id,
                    'favoriteable_type' => 'listing',
                    'favoriteable_id' => $listing->id,
                    'added_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }

    /**
     * Seed favorites for experiences.
     */
    private function seedExperienceFavorites(): void
    {
        $experiences = Experience::query()->inRandomOrder()->limit(3)->get();

        if ($experiences->isEmpty()) {
            Log::warning('No experiences found for seeding favorites');

            return;
        }

        foreach ($experiences as $experience) {
            $users = User::query()
                ->where('id', '!=', $experience->host_id)
                ->inRandomOrder()
                ->limit(3)
                ->get();

            if ($users->isEmpty()) {
                Log::warning("No eligible users found for experience {$experience->id}");

                continue;
            }

            foreach ($users as $user) {
                Favorite::factory()->create([
                    'user_id' => $user->id,
                    'favoriteable_type' => 'experience',
                    'favoriteable_id' => $experience->id,
                    'added_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
