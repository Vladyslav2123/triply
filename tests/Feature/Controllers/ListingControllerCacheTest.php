<?php

namespace Tests\Feature\Controllers;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ListingControllerCacheTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    private Listing $listing;

    /**
     * Test that featured listings are cached
     */
    public function test_featured_listings_are_cached(): void
    {
        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        $this->assertTrue(Cache::has('featured_listings'));

        $newListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => true,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($newListing->id, $listingIds);

        Cache::forget('featured_listings');

        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($newListing->id, $listingIds);
    }

    /**
     * Test that similar listings are cached
     */
    public function test_similar_listings_are_cached(): void
    {
        $similarListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => $this->listing->type,
            'price_per_night' => $this->listing->price_per_night->getAmount(),
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        $cacheKey = 'similar_listings_'.$this->listing->id;
        $this->assertTrue(Cache::has($cacheKey));

        $newSimilarListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => $this->listing->type,
            'price_per_night' => $this->listing->price_per_night->getAmount(),
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        if (count($listingIds) > 0) {
            $this->assertNotContains($newSimilarListing->id, $listingIds);
        }

        Cache::forget($cacheKey);

        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        $this->assertTrue(Cache::has($cacheKey));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create([
            'role' => 'host',
        ]);

        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => true,
            'is_published' => true,
        ]);

        Cache::flush();
    }
}
