<?php

namespace Tests\Feature\Controllers;

use App\Enums\Amenity;
use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingAmenitiesTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a host user
        $this->host = User::factory()->create([
            'role' => 'host',
        ]);

        // Create a listing with specific amenities
        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen', 'essentials'],
                Amenity::BATHROOM->value => ['hot_water', 'shampoo'],
                Amenity::OUTDOOR->value => ['bbq_grill', 'patio_or_balcony'],
            ],
        ]);
    }

    /**
     * Test filtering listings by amenity type
     */
    public function test_filter_listings_by_amenity_type(): void
    {
        // Create another listing with different amenities
        $listingWithoutOutdoor = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen'],
                Amenity::BATHROOM->value => ['hot_water'],
                // No outdoor amenities
            ],
        ]);

        // Filter by outdoor amenity type
        $response = $this->getJson('/api/v1/listings?amenity_type='.Amenity::OUTDOOR->value);

        $response->assertStatus(200);

        // Extract listing IDs from response
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();

        // Check that our listing with outdoor amenities is included
        $this->assertContains($this->listing->id, $listingIds);

        // Check that the listing without outdoor amenities is not included
        $this->assertNotContains($listingWithoutOutdoor->id, $listingIds);
    }

    /**
     * Test filtering listings by specific amenity subtype
     */
    public function test_filter_listings_by_amenity_subtype(): void
    {
        // Create another listing with different amenities
        $listingWithoutBBQ = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen'],
                Amenity::OUTDOOR->value => ['patio_or_balcony'], // No BBQ
            ],
        ]);

        // Filter by BBQ grill amenity
        $response = $this->getJson('/api/v1/listings?amenities[]=bbq_grill');

        $response->assertStatus(200);

        // Extract listing IDs from response
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();

        // Check that our listing with BBQ is included
        $this->assertContains($this->listing->id, $listingIds);

        // Check that the listing without BBQ is not included
        $this->assertNotContains($listingWithoutBBQ->id, $listingIds);
    }

    /**
     * Test filtering listings by multiple amenity subtypes
     */
    public function test_filter_listings_by_multiple_amenity_subtypes(): void
    {
        // Create another listing with only some of the amenities
        $listingWithSomeAmenities = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi'], // Has wifi but no kitchen
                Amenity::BATHROOM->value => ['hot_water', 'shampoo'],
            ],
        ]);

        // Filter by both wifi and kitchen
        $response = $this->getJson('/api/v1/listings?amenities[]=wifi&amenities[]=kitchen');

        $response->assertStatus(200);

        // Extract listing IDs from response
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();

        // Check that our listing with both wifi and kitchen is included
        $this->assertContains($this->listing->id, $listingIds);

        // Check that the listing with only wifi is not included
        $this->assertNotContains($listingWithSomeAmenities->id, $listingIds);
    }

    /**
     * Test creating a listing with amenities
     */
    public function test_create_listing_with_amenities(): void
    {
        $listingData = [
            'title' => 'Test Listing with Amenities',
            'description' => 'This is a test listing with specific amenities',
            'price_per_night' => 10000, // 100.00 in cents
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Kyiv',
                'address' => 'Test Address',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
            ],
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'tv', 'kitchen'],
                Amenity::BATHROOM->value => ['hot_water', 'hair_dryer'],
                Amenity::HEATING_COOLING->value => ['air_conditioning', 'heating'],
                Amenity::HOME_SAFETY->value => ['smoke_alarm', 'fire_extinguisher'],
            ],
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/listings', $listingData);

        $response->assertStatus(201);

        // Get the created listing ID
        $listingId = $response->json('id');

        // Check that amenities were saved correctly
        $this->assertDatabaseHas('listings', [
            'id' => $listingId,
            'title' => 'Test Listing with Amenities',
        ]);

        // Fetch the listing to check amenities
        $createdListing = Listing::find($listingId);
        $this->assertNotNull($createdListing);

        $amenities = $createdListing->amenities;
        $this->assertIsArray($amenities);
        $this->assertArrayHasKey(Amenity::BASICS->value, $amenities);
        $this->assertArrayHasKey(Amenity::BATHROOM->value, $amenities);
        $this->assertArrayHasKey(Amenity::HEATING_COOLING->value, $amenities);
        $this->assertArrayHasKey(Amenity::HOME_SAFETY->value, $amenities);

        // Check specific amenities
        $this->assertContains('wifi', $amenities[Amenity::BASICS->value]);
        $this->assertContains('kitchen', $amenities[Amenity::BASICS->value]);
        $this->assertContains('hot_water', $amenities[Amenity::BATHROOM->value]);
        $this->assertContains('heating', $amenities[Amenity::HEATING_COOLING->value]);
        $this->assertContains('smoke_alarm', $amenities[Amenity::HOME_SAFETY->value]);
    }

    /**
     * Test updating a listing's amenities
     */
    public function test_update_listing_amenities(): void
    {
        $updateData = [
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'tv', 'kitchen'], // Added TV
                Amenity::BATHROOM->value => ['hot_water', 'shampoo', 'hair_dryer'], // Added hair dryer
                Amenity::OUTDOOR->value => ['bbq_grill'], // Removed patio_or_balcony
                Amenity::HOME_SAFETY->value => ['smoke_alarm', 'fire_extinguisher'], // New category
            ],
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/listings/{$this->listing->id}", $updateData);

        $response->assertStatus(200);

        // Refresh the listing from database
        $this->listing->refresh();

        // Check that amenities were updated correctly
        $amenities = $this->listing->amenities;
        $this->assertIsArray($amenities);

        // Check updated amenities
        $this->assertContains('tv', $amenities[Amenity::BASICS->value]);
        $this->assertContains('hair_dryer', $amenities[Amenity::BATHROOM->value]);
        $this->assertNotContains('patio_or_balcony', $amenities[Amenity::OUTDOOR->value]);
        $this->assertArrayHasKey(Amenity::HOME_SAFETY->value, $amenities);
        $this->assertContains('smoke_alarm', $amenities[Amenity::HOME_SAFETY->value]);
    }

    /**
     * Test searching for listings with specific amenities
     */
    public function test_search_listings_with_amenities(): void
    {
        // Create listings with different amenities for searching
        Listing::factory()->create([
            'title' => 'Luxury Villa with Pool',
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen'],
                Amenity::PARKING_FACILITIES->value => ['pool', 'hot_tub'],
            ],
        ]);

        Listing::factory()->create([
            'title' => 'Cozy Apartment in City Center',
            'status' => ListingStatus::ACTIVE,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'tv'],
                Amenity::INTERNET_OFFICE->value => ['dedicated_workspace'],
            ],
        ]);

        // Search for listings with pool
        $response = $this->getJson('/api/v1/listings?search=pool&amenities[]=pool');

        $response->assertStatus(200);

        // Check that we found the listing with pool
        $this->assertEquals('Luxury Villa with Pool', $response->json('data.0.title'));

        // Check that we didn't find the apartment without pool
        $this->assertCount(1, $response->json('data'));
    }
}
