<?php

namespace Tests\Feature\Api\Listing;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingSortingTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a host
        $this->host = User::factory()->create([
            'role' => 'host',
        ]);
    }

    /**
     * Test sorting listings by price
     */
    public function test_sort_by_price(): void
    {
        // Create listings with different prices
        $expensiveListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'price_per_night' => 20000, // 200.00
        ]);

        $cheapListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'price_per_night' => 5000, // 50.00
        ]);

        $mediumListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'price_per_night' => 10000, // 100.00
        ]);

        // Test ascending sort
        $response = $this->getJson('/api/v1/listings?sort=price_asc');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($cheapListing->id, $listingIds[0]);
        $this->assertEquals($mediumListing->id, $listingIds[1]);
        $this->assertEquals($expensiveListing->id, $listingIds[2]);

        // Test descending sort
        $response = $this->getJson('/api/v1/listings?sort=price_desc');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($expensiveListing->id, $listingIds[0]);
        $this->assertEquals($mediumListing->id, $listingIds[1]);
        $this->assertEquals($cheapListing->id, $listingIds[2]);
    }

    /**
     * Test sorting listings by title
     */
    public function test_sort_by_title(): void
    {
        // Create listings with different titles
        $listingA = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'title' => 'A Luxury Apartment',
        ]);

        $listingB = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'title' => 'B Cozy Cottage',
        ]);

        $listingC = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'title' => 'C Modern Villa',
        ]);

        // Test ascending sort
        $response = $this->getJson('/api/v1/listings?sort=title_asc');
        $response->assertStatus(200);

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals('A Luxury Apartment', $titles[0]);
        $this->assertEquals('B Cozy Cottage', $titles[1]);
        $this->assertEquals('C Modern Villa', $titles[2]);

        // Test descending sort
        $response = $this->getJson('/api/v1/listings?sort=title_desc');
        $response->assertStatus(200);

        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals('C Modern Villa', $titles[0]);
        $this->assertEquals('B Cozy Cottage', $titles[1]);
        $this->assertEquals('A Luxury Apartment', $titles[2]);
    }

    /**
     * Test sorting listings by creation date
     */
    public function test_sort_by_creation_date(): void
    {
        // Create listings with different creation dates
        $oldListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'created_at' => now()->subDays(30),
        ]);

        $mediumListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'created_at' => now()->subDays(15),
        ]);

        $newListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'created_at' => now()->subDays(5),
        ]);

        // Test ascending sort
        $response = $this->getJson('/api/v1/listings?sort=created_at_asc');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($oldListing->id, $listingIds[0]);
        $this->assertEquals($mediumListing->id, $listingIds[1]);
        $this->assertEquals($newListing->id, $listingIds[2]);

        // Test descending sort
        $response = $this->getJson('/api/v1/listings?sort=created_at_desc');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($newListing->id, $listingIds[0]);
        $this->assertEquals($mediumListing->id, $listingIds[1]);
        $this->assertEquals($oldListing->id, $listingIds[2]);
    }

    /**
     * Test sorting listings by popularity (views count)
     */
    public function test_sort_by_popularity(): void
    {
        // Create listings with different view counts
        $popularListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 100,
        ]);

        $mediumPopularListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 50,
        ]);

        $unpopularListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 10,
        ]);

        // Test popularity sort (descending by views_count)
        $response = $this->getJson('/api/v1/listings?sort=popularity');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($popularListing->id, $listingIds[0]);
        $this->assertEquals($mediumPopularListing->id, $listingIds[1]);
        $this->assertEquals($unpopularListing->id, $listingIds[2]);
    }

    /**
     * Test sorting listings by rating
     */
    public function test_sort_by_rating(): void
    {
        // Create a guest for reviews
        $guest = User::factory()->create();

        // Create listings with different ratings
        $highRatedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $highRatedListing->id,
            'reservationable_type' => 'listing',
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $guest->id,
            'overall_rating' => 5.0,
        ]);

        $mediumRatedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $mediumRatedListing->id,
            'reservationable_type' => 'listing',
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $guest->id,
            'overall_rating' => 3.0,
        ]);

        $lowRatedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $reservation3 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $lowRatedListing->id,
            'reservationable_type' => 'listing',
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation3->id,
            'reviewer_id' => $guest->id,
            'overall_rating' => 1.0,
        ]);

        // Test ascending sort
        $response = $this->getJson('/api/v1/listings?sort=rating_asc');
        $response->assertStatus(200);

        // Get the ratings from the response
        $ratings = collect($response->json('data'))->pluck('avg_rating')->toArray();

        // Check that ratings are in ascending order
        $this->assertLessThanOrEqual($ratings[1], $ratings[2]);
        $this->assertLessThanOrEqual($ratings[0], $ratings[1]);

        // Test descending sort
        $response = $this->getJson('/api/v1/listings?sort=rating_desc');
        $response->assertStatus(200);

        // Get the ratings from the response
        $ratings = collect($response->json('data'))->pluck('avg_rating')->toArray();

        // Check that ratings are in descending order
        $this->assertGreaterThanOrEqual($ratings[1], $ratings[0]);
        $this->assertGreaterThanOrEqual($ratings[2], $ratings[1]);
    }
}
