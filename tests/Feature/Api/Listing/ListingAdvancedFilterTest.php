<?php

namespace Tests\Feature\Api\Listing;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;
use App\ValueObjects\Listing\AccessibilityFeature;
use App\ValueObjects\Listing\GuestSafety;
use App\ValueObjects\Listing\RoomRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingAdvancedFilterTest extends TestCase
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
     * Test filtering listings by accessibility features
     */
    public function test_filter_by_accessibility_features(): void
    {
        // Create a listing with specific accessibility features
        $listingWithAccessibility = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'accessibility_features' => new AccessibilityFeature(
                disabledParkingSpot: true,
                guestEntrance: true,
                stepFreeAccess: false,
                swimmingPool: false,
                ceilingHoist: false
            ),
        ]);

        // Create a listing without those accessibility features
        $listingWithoutAccessibility = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'accessibility_features' => new AccessibilityFeature(
                disabledParkingSpot: false,
                guestEntrance: false,
                stepFreeAccess: false,
                swimmingPool: false,
                ceilingHoist: false
            ),
        ]);

        // Test filtering by disabled_parking_spot
        $response = $this->getJson('/api/v1/listings?accessibility_features[disabled_parking_spot]=true');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($listingWithAccessibility->id, $listingIds);
        $this->assertNotContains($listingWithoutAccessibility->id, $listingIds);

        // Test filtering by guest_entrance
        $response = $this->getJson('/api/v1/listings?accessibility_features[guest_entrance]=true');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($listingWithAccessibility->id, $listingIds);
        $this->assertNotContains($listingWithoutAccessibility->id, $listingIds);
    }

    /**
     * Test filtering listings by property size
     */
    public function test_filter_by_property_size(): void
    {
        // Create a listing with a small property size
        $smallListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => new RoomRule(
                floorsCount: 1,
                floorListing: 1,
                yearBuilt: 2000,
                propertySize: 50.0
            ),
        ]);

        // Create a listing with a large property size
        $largeListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => new RoomRule(
                floorsCount: 2,
                floorListing: 1,
                yearBuilt: 2010,
                propertySize: 150.0
            ),
        ]);

        // Test filtering by minimum property size
        $response = $this->getJson('/api/v1/listings?property_size_min=100');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($smallListing->id, $listingIds);
        $this->assertContains($largeListing->id, $listingIds);

        // Test filtering by maximum property size
        $response = $this->getJson('/api/v1/listings?property_size_max=100');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($smallListing->id, $listingIds);
        $this->assertNotContains($largeListing->id, $listingIds);

        // Test filtering by property size range
        $response = $this->getJson('/api/v1/listings?property_size_min=40&property_size_max=60');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($smallListing->id, $listingIds);
        $this->assertNotContains($largeListing->id, $listingIds);
    }

    /**
     * Test filtering listings by year built
     */
    public function test_filter_by_year_built(): void
    {
        // Create a listing with an older year built
        $oldListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => new RoomRule(
                floorsCount: 1,
                floorListing: 1,
                yearBuilt: 1980,
                propertySize: 100.0
            ),
        ]);

        // Create a listing with a newer year built
        $newListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => new RoomRule(
                floorsCount: 2,
                floorListing: 1,
                yearBuilt: 2020,
                propertySize: 100.0
            ),
        ]);

        // Test filtering by minimum year built
        $response = $this->getJson('/api/v1/listings?year_built_min=2000');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($oldListing->id, $listingIds);
        $this->assertContains($newListing->id, $listingIds);

        // Test filtering by maximum year built
        $response = $this->getJson('/api/v1/listings?year_built_max=2000');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($oldListing->id, $listingIds);
        $this->assertNotContains($newListing->id, $listingIds);

        // Test filtering by year built range
        $response = $this->getJson('/api/v1/listings?year_built_min=1970&year_built_max=1990');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($oldListing->id, $listingIds);
        $this->assertNotContains($newListing->id, $listingIds);
    }

    /**
     * Test filtering listings by guest safety features
     */
    public function test_filter_by_guest_safety(): void
    {
        // Create a listing with specific guest safety features
        $safetyListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'guest_safety' => new GuestSafety(
                smokeDetector: true,
                fireExtinguisher: true,
                securityCamera: false
            ),
        ]);

        // Create a listing without those guest safety features
        $nonSafetyListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'guest_safety' => new GuestSafety(
                smokeDetector: false,
                fireExtinguisher: false,
                securityCamera: true
            ),
        ]);

        // Test filtering by smoke_detector
        $response = $this->getJson('/api/v1/listings?guest_safety[smoke_detector]=true');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($safetyListing->id, $listingIds);
        $this->assertNotContains($nonSafetyListing->id, $listingIds);

        // Test filtering by fire_extinguisher
        $response = $this->getJson('/api/v1/listings?guest_safety[fire_extinguisher]=true');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($safetyListing->id, $listingIds);
        $this->assertNotContains($nonSafetyListing->id, $listingIds);

        // Test filtering by security_camera
        $response = $this->getJson('/api/v1/listings?guest_safety[security_camera]=true');
        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($safetyListing->id, $listingIds);
        $this->assertContains($nonSafetyListing->id, $listingIds);
    }
}
