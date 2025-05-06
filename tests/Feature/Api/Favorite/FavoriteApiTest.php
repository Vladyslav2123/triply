<?php

namespace Tests\Feature\Api\Favorite;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Listing $listing;

    private Experience $experience;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $host = User::factory()->create();
        $this->listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);
        $this->experience = Experience::factory()->create([
            'host_id' => $host->id,
        ]);
    }

    public function test_user_can_favorite_listing(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/listings/{$this->listing->id}/favorite");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'favoriteable_id',
                'favoriteable_type',
                'created_at',
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);
    }

    public function test_user_can_favorite_experience(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/experiences/{$this->experience->id}/favorite");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'favoriteable_id',
                'favoriteable_type',
                'created_at',
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->experience->id,
            'favoriteable_type' => 'experience',
        ]);
    }

    public function test_unauthenticated_user_cannot_favorite(): void
    {
        $response = $this->postJson("/api/v1/listings/{$this->listing->id}/favorite");

        $response->assertStatus(401);
    }

    public function test_user_can_unfavorite_listing(): void
    {
        // First favorite the listing
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/listings/{$this->listing->id}/favorite");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);
    }

    public function test_user_can_unfavorite_experience(): void
    {
        // First favorite the experience
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->experience->id,
            'favoriteable_type' => 'experience',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/experiences/{$this->experience->id}/favorite");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->experience->id,
            'favoriteable_type' => 'experience',
        ]);
    }

    public function test_user_can_get_favorite_listings(): void
    {
        // Create favorite listings
        $listing1 = Listing::factory()->create();
        $listing2 = Listing::factory()->create();

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $listing1->id,
            'favoriteable_type' => 'listing',
        ]);

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $listing2->id,
            'favoriteable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/favorites/listings');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price_per_night',
                        'host',
                        'location',
                    ],
                ],
            ]);
    }

    public function test_user_can_get_favorite_experiences(): void
    {
        // Create favorite experiences
        $experience1 = Experience::factory()->create();
        $experience2 = Experience::factory()->create();

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $experience1->id,
            'favoriteable_type' => 'experience',
        ]);

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $experience2->id,
            'favoriteable_type' => 'experience',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/favorites/experiences');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'host',
                        'location',
                        'category',
                    ],
                ],
            ]);
    }

    public function test_user_can_get_all_favorites(): void
    {
        // Create favorite listings and experiences
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);

        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->experience->id,
            'favoriteable_type' => 'experience',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/favorites');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'favoriteable_id',
                        'favoriteable_type',
                        'created_at',
                        'favoriteable' => [
                            'id',
                            'title',
                        ],
                    ],
                ],
            ]);
    }

    public function test_user_can_check_if_item_is_favorited(): void
    {
        // Favorite the listing
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/listings/{$this->listing->id}/is-favorited");

        $response->assertStatus(200)
            ->assertJson([
                'is_favorited' => true,
            ]);

        // Check an unfavorited item
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/experiences/{$this->experience->id}/is-favorited");

        $response->assertStatus(200)
            ->assertJson([
                'is_favorited' => false,
            ]);
    }

    public function test_user_can_get_favorite_count_for_item(): void
    {
        // Multiple users favorite the same listing
        Favorite::factory()->create([
            'user_id' => $this->user->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);

        $otherUser = User::factory()->create();
        Favorite::factory()->create([
            'user_id' => $otherUser->id,
            'favoriteable_id' => $this->listing->id,
            'favoriteable_type' => 'listing',
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/favorite-count");

        $response->assertStatus(200)
            ->assertJson([
                'count' => 2,
            ]);
    }
}
