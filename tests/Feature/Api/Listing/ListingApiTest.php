<?php

namespace Tests\Feature\Api\Listing;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $host;

    public function test_can_get_listings_list(): void
    {
        Listing::factory()->count(5)->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price_per_night',
                        'host',
                        'location',
                        'status',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_get_single_listing(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson("/api/v1/listings/{$listing->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price_per_night',
                    'host',
                    'location',
                    'status',
                    'amenities',
                    'house_rules',
                    'photos',
                ],
            ])
            ->assertJsonPath('data.id', $listing->id);
    }

    public function test_can_create_listing_when_authenticated_as_host(): void
    {
        $listingData = [
            'title' => 'Test Listing',
            'description' => [
                'listing_description' => 'This is a test listing description',
                'your_property' => 'This is a test property description',
                'guest_access' => 'This is a test guest access description',
                'interaction_with_guests' => 'This is a test interaction with guests description',
                'other_details' => 'This is a test other details description',
            ],
            'price_per_night' => [
                'amount' => '10000',
                'currency' => 'UAH',
            ],
            'discounts' => [
                'weekly' => 5,
                'monthly' => 10,
            ],
            'accept_guests' => [
                'adults' => true,
                'children' => true,
                'pets' => false,
            ],
            'rooms_rules' => [
                'floors_count' => 1,
                'floor_listing' => 1,
                'year_built' => 2000,
                'property_size' => 100.0,
            ],
            'location' => [
                'address' => [
                    'street' => 'Test Street',
                    'city' => 'Kyiv',
                    'postal_code' => '01001',
                    'country' => 'Ukraine',
                    'state' => 'Test State',
                ],
                'coordinates' => [
                    'latitude' => 50.4501,
                    'longitude' => 30.5234,
                ],
            ],
            'type' => 'apartment',
            'subtype' => 'rental_unit',
            'listing_type' => ListingType::ENTIRE_PLACE->value,
            'advance_notice_type' => 'same_day',
            'amenities' => [
                'basics' => ['wifi', 'kitchen', 'heating'],
                'bathroom' => ['hot_water'],
                'home_safety' => ['smoke_alarm'],
            ],
            'accessibility_features' => [
                'disabled_parking_spot' => true,
                'guest_entrance' => true,
                'step_free_access' => false,
                'swimming_pool' => false,
                'ceiling_hoist' => false,
            ],
            'availability_settings' => [
                'min_stay' => 1,
                'max_stay' => 30,
            ],
            'house_rules' => [
                'pets_allowed' => true,
                'events_allowed' => false,
                'smoking_allowed' => false,
                'quiet_hours' => true,
                'commercial_photography_allowed' => false,
                'number_of_guests' => 3,
                'additional_rules' => 'No loud music after 10 PM',
            ],
            'guest_safety' => [
                'smoke_detector' => true,
                'fire_extinguisher' => true,
                'security_camera' => false,
            ],
            'host_id' => $this->host->id,
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/listings', $listingData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'price_per_night',
                'host',
                'location',
                'status',
            ]);

        $this->assertDatabaseHas('listings', [
            'title' => 'Test Listing',
            'host_id' => $this->host->id,
        ]);
    }

    public function test_cannot_create_listing_when_unauthenticated(): void
    {
        $listingData = [
            'title' => 'Test Listing',
            'description' => 'This is a test listing description',
            'price_per_night' => 10000,
        ];

        $response = $this->postJson('/api/v1/listings', $listingData);

        $response->assertStatus(401);
    }

    public function test_can_update_own_listing(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'title' => 'Original Title',
            'status' => ListingStatus::PUBLISHED,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => [
                'listing_description' => 'Updated description',
                'your_property' => 'Updated property description',
                'guest_access' => 'Updated guest access description',
                'interaction_with_guests' => 'Updated interaction with guests description',
                'other_details' => 'Updated other details description',
            ],
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/listings/{$listing->slug}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.description.listing_description', 'Updated description');

        $this->assertDatabaseHas('listings', [
            'id' => $listing->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_cannot_update_others_listing(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->actingAs($this->user) // Not the host
            ->putJson("/api/v1/listings/{$listing->slug}", $updateData);

        $response->assertStatus(403);
    }

    public function test_can_delete_own_listing(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/listings/{$listing->slug}");

        $response->assertStatus(204);

        // The listing should be deleted from the database
        $this->assertDatabaseMissing('listings', [
            'id' => $listing->id,
        ]);
    }

    public function test_cannot_delete_others_listing(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/listings/{$listing->slug}");

        $response->assertStatus(403);
    }

    public function test_can_filter_listings_by_location(): void
    {
        $kyivListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'title' => 'Apartment in Kyiv',
        ]);

        $lvivListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'title' => 'House in Lviv',
        ]);

        $response = $this->getJson('/api/v1/listings?search=Kyiv');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertContains('Apartment in Kyiv', $titles);
        $this->assertNotContains('House in Lviv', $titles);
    }

    public function test_can_filter_listings_by_price_range(): void
    {
        Listing::factory()->create([
            'host_id' => $this->host->id,
            'price_per_night' => 5000,
        ]);

        Listing::factory()->create([
            'host_id' => $this->host->id,
            'price_per_night' => 15000,
        ]);

        $response = $this->getJson('/api/v1/listings?price_min=10000&price_max=20000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_listings_by_amenities(): void
    {
        Listing::factory()->create([
            'host_id' => $this->host->id,
            'amenities' => ['wifi', 'pool'],
        ]);

        Listing::factory()->create([
            'host_id' => $this->host->id,
            'amenities' => ['wifi', 'kitchen'],
        ]);

        $response = $this->getJson('/api/v1/listings?amenities[]=pool');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_sort_listings_by_price(): void
    {
        $expensive = Listing::factory()->create([
            'host_id' => $this->host->id,
            'price_per_night' => 20000,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $cheap = Listing::factory()->create([
            'host_id' => $this->host->id,
            'price_per_night' => 5000,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=price_asc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $cheap->id);

        $response = $this->getJson('/api/v1/listings?sort=price_desc');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $expensive->id);
    }

    public function test_can_sort_listings_by_title(): void
    {
        $listingA = Listing::factory()->create([
            'host_id' => $this->host->id,
            'title' => 'A Luxury Apartment',
            'status' => ListingStatus::PUBLISHED,
        ]);

        $listingB = Listing::factory()->create([
            'host_id' => $this->host->id,
            'title' => 'B Cozy Cottage',
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=title_asc');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals('A Luxury Apartment', $titles[0]);
        $this->assertEquals('B Cozy Cottage', $titles[1]);

        $response = $this->getJson('/api/v1/listings?sort=title_desc');

        $response->assertStatus(200);
        $titles = collect($response->json('data'))->pluck('title')->toArray();
        $this->assertEquals('B Cozy Cottage', $titles[0]);
        $this->assertEquals('A Luxury Apartment', $titles[1]);
    }

    public function test_can_sort_listings_by_popularity(): void
    {
        $popularListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'views_count' => 100,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $unpopularListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'views_count' => 10,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=popularity');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($popularListing->id, $listingIds[0]);
        $this->assertEquals($unpopularListing->id, $listingIds[1]);
    }

    public function test_can_get_host_listings(): void
    {
        // Clear existing listings
        Listing::where('host_id', $this->host->id)->delete();

        // Create exactly 3 listings for this host
        Listing::factory()->count(3)->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        // Create listings for another host
        $otherHost = User::factory()->create(['role' => 'host']);
        Listing::factory()->count(2)->create([
            'host_id' => $otherHost->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson("/api/v1/hosts/{$this->host->id}/listings");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_listings_by_accessibility_features(): void
    {
        $listingWithAccessibility = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'accessibility_features' => [
                'disabled_parking_spot' => true,
                'guest_entrance' => true,
                'step_free_access' => false,
            ],
        ]);

        $listingWithoutAccessibility = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'accessibility_features' => [
                'disabled_parking_spot' => false,
                'guest_entrance' => false,
                'step_free_access' => false,
            ],
        ]);

        $response = $this->getJson('/api/v1/listings?accessibility_features[disabled_parking_spot]=true');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($listingWithAccessibility->id, $listingIds);
        $this->assertNotContains($listingWithoutAccessibility->id, $listingIds);
    }

    public function test_can_filter_listings_by_property_size(): void
    {
        $smallListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => [
                'property_size' => 50.0,
                'year_built' => 2000,
                'floors_count' => 1,
                'floor_listing' => 1,
            ],
        ]);

        $largeListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'rooms_rules' => [
                'property_size' => 150.0,
                'year_built' => 2010,
                'floors_count' => 2,
                'floor_listing' => 1,
            ],
        ]);

        $response = $this->getJson('/api/v1/listings?property_size_min=100');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($smallListing->id, $listingIds);
        $this->assertContains($largeListing->id, $listingIds);
    }

    public function test_can_filter_listings_by_guest_safety(): void
    {
        $safeListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'guest_safety' => [
                'smoke_detector' => true,
                'fire_extinguisher' => true,
                'security_camera' => false,
            ],
        ]);

        $unsafeListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'guest_safety' => [
                'smoke_detector' => false,
                'fire_extinguisher' => false,
                'security_camera' => true,
            ],
        ]);

        $response = $this->getJson('/api/v1/listings?guest_safety[smoke_detector]=true');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($safeListing->id, $listingIds);
        $this->assertNotContains($unsafeListing->id, $listingIds);
    }

    public function test_can_get_featured_listings(): void
    {
        $featuredListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => true,
            'is_published' => true,
        ]);

        $nonFeaturedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => false,
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/listings/featured');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($featuredListing->id, $listingIds);
        $this->assertNotContains($nonFeaturedListing->id, $listingIds);
    }

    public function test_can_get_similar_listings(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => 'apartment',
            'price_per_night' => 10000,
        ]);

        $similarListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => 'apartment',
            'price_per_night' => 15000,
        ]);

        $differentListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => 'house',
            'price_per_night' => 10000,
        ]);

        $response = $this->getJson("/api/v1/listings/{$listing->slug}/similar");

        $response->assertStatus(200);
        $listingTypes = collect($response->json('data'))->pluck('type')->toArray();
        $this->assertContains('apartment', $listingTypes);
        $this->assertNotContains('house', $listingTypes);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->host = User::factory()->create(['role' => 'host']);
    }
}
