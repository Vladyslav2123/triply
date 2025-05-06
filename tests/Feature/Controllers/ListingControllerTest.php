<?php

namespace Tests\Feature\Controllers;

use App\Enums\Amenity;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Listing;
use App\Models\User;
use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Listing\AccessibilityFeature;
use App\ValueObjects\Listing\GuestSafety;
use App\ValueObjects\Listing\RoomRule;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ListingControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $host;

    private Listing $listing;

    /**
     * Тест отримання списку оголошень
     */
    public function test_index_returns_paginated_listings(): void
    {
        Listing::factory()->count(5)->create([
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

    /**
     * Тест отримання деталей оголошення
     */
    public function test_show_returns_listing_details(): void
    {
        $response = $this->getJson("/api/v1/listings/{$this->listing->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
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
            ])
            ->assertJsonPath('id', $this->listing->id);
    }

    /**
     * Тест створення нового оголошення
     */
    public function test_store_creates_new_listing(): void
    {
        $listingData = [
            'title' => 'Test Listing',
            'description' => 'This is a test listing description',
            'price_per_night' => 10000,
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Kyiv',
                'address' => 'Test Address',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
            ],
            'type' => 'apartment',
            'listing_type' => ListingType::ENTIRE_PLACE->value,
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen', 'heating'],
                Amenity::BATHROOM->value => ['hot_water'],
                Amenity::HOME_SAFETY->value => ['smoke_alarm'],
            ],
            'house_rules' => [
                'check_in_time' => '14:00',
                'check_out_time' => '12:00',
                'pets_allowed' => true,
                'smoking_allowed' => false,
                'parties_allowed' => false,
            ],
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

    /**
     * Тест валідації при створенні оголошення
     */
    public function test_store_validates_listing_data(): void
    {
        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/listings', [
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'price_per_night']);
    }

    /**
     * Тест оновлення оголошення
     */
    public function test_update_modifies_listing(): void
    {
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'price_per_night' => 15000,
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/listings/{$this->listing->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Title')
            ->assertJsonPath('description', 'Updated description')
            ->assertJsonPath('price_per_night.amount', 15000);

        $this->assertDatabaseHas('listings', [
            'id' => $this->listing->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);
    }

    /**
     * Тест заборони оновлення чужого оголошення
     */
    public function test_update_forbidden_for_others_listing(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->putJson("/api/v1/listings/{$this->listing->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Тест видалення оголошення
     */
    public function test_destroy_removes_listing(): void
    {
        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/listings/{$this->listing->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('listings', [
            'id' => $this->listing->id,
        ]);
    }

    /**
     * Тест заборони видалення чужого оголошення
     */
    public function test_destroy_forbidden_for_others_listing(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/listings/{$this->listing->id}");

        $response->assertStatus(403);
    }

    /**
     * Тест фільтрації оголошень за місцем розташування (пошук)
     */
    public function test_index_filters_listings_by_location(): void
    {
        $kyivLocation = new Location(
            new Address(
                street: 'Test Street',
                city: 'Kyiv',
                postalCode: '01001',
                country: 'Ukraine',
                state: 'Test State'
            ),
            new Coordinates(
                latitude: 50.4501,
                longitude: 30.5234)
        );

        $lvivLocation = new Location(
            new Address(
                street: 'Test Street',
                city: 'Lviv',
                postalCode: '01001',
                country: 'Ukraine',
                state: 'Test State'
            ),
            new Coordinates(
                latitude: 49.8397,
                longitude: 24.0297)
        );

        Listing::factory()->create([
            'location' => $kyivLocation,
            'status' => ListingStatus::PUBLISHED,
        ]);

        Listing::factory()->create([
            'location' => $lvivLocation,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?location[address][city]=Kyiv');

        Log::info($response->json());

        $response->assertStatus(200);
        $cities = collect($response->json('data'))->pluck('location.address.city')->toArray();
        $this->assertContains('Kyiv', $cities);
    }

    /**
     * Тест фільтрації оголошень за координатами
     */
    public function test_index_filters_listings_by_coordinates(): void
    {
        $kyivLocation = new Location(
            new Address(
                street: 'Test Street',
                city: 'Kyiv',
                postalCode: '01001',
                country: 'Ukraine',
                state: 'Test State'
            ),
            new Coordinates(
                latitude: 50.4501,
                longitude: 30.5234)
        );

        $lvivLocation = new Location(
            new Address(
                street: 'Test Street',
                city: 'Lviv',
                postalCode: '01001',
                country: 'Ukraine',
                state: 'Test State'
            ),
            new Coordinates(
                latitude: 49.8397,
                longitude: 24.0297)
        );

        Listing::factory()->create([
            'location' => $kyivLocation,
            'status' => ListingStatus::PUBLISHED,
        ]);

        Listing::factory()->create([
            'location' => $lvivLocation,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?location[coordinates][latitude]=50.4501&location[coordinates][longitude]=30.5234&location[radius]=10');

        $response->assertStatus(200);
        $cities = collect($response->json('data'))->pluck('location.address.city')->toArray();
        $this->assertContains('Kyiv', $cities);
        $this->assertNotContains('Lviv', $cities);
    }

    /**
     * Тест фільтрації оголошень за ціною
     */
    public function test_index_filters_listings_by_price_range(): void
    {
        Listing::factory()->create([
            'price_per_night' => 5000,
            'status' => ListingStatus::PUBLISHED,
        ]);

        Listing::factory()->create([
            'price_per_night' => 15000, // 150.00
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?price_min=10000&price_max=20000');

        $response->assertStatus(200);
        $this->assertEquals(15000, $response->json('data.0.price_per_night.amount'));
    }

    /**
     * Тест фільтрації оголошень за зручностями
     */
    public function test_index_filters_listings_by_amenities(): void
    {
        $listingWithPool = Listing::factory()->create([
            'amenities' => [
                Amenity::BASICS->value => ['wifi'],
                Amenity::PARKING_FACILITIES->value => ['pool', 'hot_tub'],
            ],
            'status' => ListingStatus::PUBLISHED,
        ]);

        $listingWithoutPool = Listing::factory()->create([
            'amenities' => [
                Amenity::BASICS->value => ['wifi', 'kitchen'],
                Amenity::BATHROOM->value => ['hot_water'],
            ],
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?amenities[]=pool');

        $response->assertStatus(200);

        $listingIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($listingWithPool->id, $listingIds);

        $this->assertNotContains($listingWithoutPool->id, $listingIds);
    }

    /**
     * Тест сортування оголошень за ціною
     */
    public function test_index_sorts_listings_by_price(): void
    {
        $expensive = Listing::factory()->create([
            'price_per_night' => 20000,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $cheap = Listing::factory()->create([
            'price_per_night' => 5000,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=price_asc');

        $response->assertStatus(200);
        $this->assertEquals(5000, $response->json('data.0.price_per_night.amount'));

        $response = $this->getJson('/api/v1/listings?sort=price_desc');

        $response->assertStatus(200);
        $this->assertEquals(20000, $response->json('data.0.price_per_night.amount'));
    }

    /**
     * Тест сортування оголошень за назвою
     */
    public function test_index_sorts_listings_by_title(): void
    {
        $listingA = Listing::factory()->create([
            'title' => 'A Luxury Apartment',
            'status' => ListingStatus::PUBLISHED,
        ]);

        $listingB = Listing::factory()->create([
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

    /**
     * Тест сортування оголошень за датою створення
     */
    public function test_index_sorts_listings_by_creation_date(): void
    {
        $oldListing = Listing::factory()->create([
            'created_at' => now()->subDays(30),
            'status' => ListingStatus::PUBLISHED,
        ]);

        $newListing = Listing::factory()->create([
            'created_at' => now()->subDays(5),
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=created_at_asc');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($oldListing->id, $listingIds[0]);
        $this->assertEquals($newListing->id, $listingIds[1]);

        $response = $this->getJson('/api/v1/listings?sort=created_at_desc');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($newListing->id, $listingIds[0]);
        $this->assertEquals($oldListing->id, $listingIds[1]);
    }

    /**
     * Тест сортування оголошень за популярністю
     */
    public function test_index_sorts_listings_by_popularity(): void
    {
        $popularListing = Listing::factory()->create([
            'views_count' => 100,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $unpopularListing = Listing::factory()->create([
            'views_count' => 10,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?sort=popularity');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertEquals($popularListing->id, $listingIds[0]);
        $this->assertEquals($unpopularListing->id, $listingIds[1]);
    }

    /**
     * Тест отримання оголошень конкретного хоста
     */
    public function test_get_host_listings(): void
    {
        Listing::factory()->count(3)->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $otherHost = User::factory()->create(['role' => 'host']);
        Listing::factory()->count(2)->create([
            'host_id' => $otherHost->id,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson("/api/v1/hosts/{$this->host->id}/listings");

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    /**
     * Тест зміни статусу оголошення
     */
    public function test_update_listing_status(): void
    {
        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$this->listing->id}/status", [
                'status' => ListingStatus::DRAFT->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', ListingStatus::DRAFT->value);

        $this->assertDatabaseHas('listings', [
            'id' => $this->listing->id,
            'status' => ListingStatus::DRAFT,
        ]);
    }

    /**
     * Тест оновлення правил будинку
     */
    public function test_update_house_rules(): void
    {
        $houseRules = [
            'check_in_time' => '15:00',
            'check_out_time' => '11:00',
            'pets_allowed' => true,
            'smoking_allowed' => false,
            'parties_allowed' => false,
            'additional_rules' => 'No loud music after 10 PM',
        ];

        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$this->listing->id}/house-rules", $houseRules);

        $response->assertStatus(200)
            ->assertJsonPath('house_rules.check_in_time', '15:00')
            ->assertJsonPath('house_rules.check_out_time', '11:00')
            ->assertJsonPath('house_rules.pets_allowed', true);
    }

    /**
     * Тест оновлення зручностей
     */
    public function test_update_amenities(): void
    {
        $amenities = [
            'has_wifi' => true,
            'has_pool' => true,
            'has_kitchen' => true,
            'has_heating' => true,
            'has_air_conditioning' => true,
        ];

        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$this->listing->id}/amenities", $amenities);

        $response->assertStatus(200)
            ->assertJsonPath('amenities.has_wifi', true)
            ->assertJsonPath('amenities.has_pool', true)
            ->assertJsonPath('amenities.has_kitchen', true);
    }

    /**
     * Тест оновлення місця розташування
     */
    public function test_update_location(): void
    {
        $location = [
            'country' => 'Ukraine',
            'city' => 'Lviv',
            'address' => 'Test Address',
            'latitude' => 49.8397,
            'longitude' => 24.0297,
        ];

        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$this->listing->id}/location", $location);

        $response->assertStatus(200)
            ->assertJsonPath('location.country', 'Ukraine')
            ->assertJsonPath('location.city', 'Lviv')
            ->assertJsonPath('location.latitude', 49.8397)
            ->assertJsonPath('location.longitude', 24.0297);
    }

    /**
     * Тест отримання оголошень з пошуком
     */
    public function test_index_searches_listings(): void
    {
        Listing::factory()->create([
            'title' => 'Luxury Apartment in Kyiv',
            'status' => ListingStatus::PUBLISHED,
        ]);

        Listing::factory()->create([
            'title' => 'Cozy House in Lviv',
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?search=Luxury');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Luxury Apartment in Kyiv');
    }

    /**
     * Тест фільтрації оголошень за особливостями доступності
     */
    public function test_index_filters_listings_by_accessibility_features(): void
    {
        $accessibilityFeatures = new AccessibilityFeature(
            disabledParkingSpot: true,
            guestEntrance: true,
            stepFreeAccess: false,
            swimmingPool: false,
            ceilingHoist: false
        );

        $listingWithAccessibility = Listing::factory()->create([
            'accessibility_features' => $accessibilityFeatures,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $noAccessibilityFeatures = new AccessibilityFeature(
            disabledParkingSpot: false,
            guestEntrance: false,
            stepFreeAccess: false,
            swimmingPool: false,
            ceilingHoist: false
        );

        $listingWithoutAccessibility = Listing::factory()->create([
            'accessibility_features' => $noAccessibilityFeatures,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?accessibility_features[disabled_parking_spot]=true');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($listingWithAccessibility->id, $listingIds);
        $this->assertNotContains($listingWithoutAccessibility->id, $listingIds);
    }

    /**
     * Тест фільтрації оголошень за розміром нерухомості
     */
    public function test_index_filters_listings_by_property_size(): void
    {
        $smallRoomRules = new RoomRule(
            floorsCount: 1,
            floorListing: 1,
            yearBuilt: 2000,
            propertySize: 50.0
        );

        $smallListing = Listing::factory()->create([
            'rooms_rules' => $smallRoomRules,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $largeRoomRules = new RoomRule(
            floorsCount: 2,
            floorListing: 1,
            yearBuilt: 2010,
            propertySize: 150.0
        );

        $largeListing = Listing::factory()->create([
            'rooms_rules' => $largeRoomRules,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?property_size_min=100');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertNotContains($smallListing->id, $listingIds);
        $this->assertContains($largeListing->id, $listingIds);
    }

    /**
     * Тест фільтрації оголошень за безпекою гостей
     */
    public function test_index_filters_listings_by_guest_safety(): void
    {
        $safetyFeatures = new GuestSafety(
            smokeDetector: true,
            fireExtinguisher: true,
            securityCamera: false
        );

        $safeListing = Listing::factory()->create([
            'guest_safety' => $safetyFeatures,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $noSafetyFeatures = new GuestSafety(
            smokeDetector: false,
            fireExtinguisher: false,
            securityCamera: true
        );

        $unsafeListing = Listing::factory()->create([
            'guest_safety' => $noSafetyFeatures,
            'status' => ListingStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/listings?guest_safety[smoke_detector]=true');

        $response->assertStatus(200);
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($safeListing->id, $listingIds);
        $this->assertNotContains($unsafeListing->id, $listingIds);
    }

    /**
     * Тест отримання рекомендованих оголошень
     */
    public function test_get_recommended_listings(): void
    {
        $listing1 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'average_rating' => 4.9,
        ]);

        $listing2 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'average_rating' => 4.8,
        ]);

        $listing3 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'average_rating' => 3.5,
        ]);

        $response = $this->getJson('/api/v1/listings/recommended');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Тест отримання популярних оголошень
     */
    public function test_get_popular_listings(): void
    {
        $listing1 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 100,
        ]);

        $listing2 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 80,
        ]);

        $listing3 = Listing::factory()->create([
            'status' => ListingStatus::PUBLISHED,
            'views_count' => 20,
        ]);

        $response = $this->getJson('/api/v1/listings/popular');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Тест інкрементування лічильника переглядів
     */
    public function test_view_increments_view_count(): void
    {
        $initialViewCount = $this->listing->views_count ?? 0;

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}");

        $response->assertStatus(200);

        $this->listing->refresh();
        $this->assertEquals($initialViewCount + 1, $this->listing->views_count);
    }

    /**
     * Тест публікації оголошення
     */
    public function test_publish_updates_listing_status(): void
    {
        $draftListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::DRAFT,
            'is_published' => false,
        ]);

        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$draftListing->slug}/publish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', ListingStatus::PUBLISHED->value)
            ->assertJsonPath('data.is_published', true);

        $this->assertDatabaseHas('listings', [
            'id' => $draftListing->id,
            'status' => ListingStatus::PUBLISHED->value,
            'is_published' => true,
        ]);
    }

    /**
     * Тест зняття оголошення з публікації
     */
    public function test_unpublish_updates_listing_status(): void
    {
        $publishedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->host)
            ->patchJson("/api/v1/listings/{$publishedListing->slug}/unpublish");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', ListingStatus::DRAFT->value)
            ->assertJsonPath('data.is_published', false);

        $this->assertDatabaseHas('listings', [
            'id' => $publishedListing->id,
            'status' => ListingStatus::DRAFT->value,
            'is_published' => false,
        ]);
    }

    /**
     * Тест заборони публікації чужого оголошення
     */
    public function test_publish_forbidden_for_others_listing(): void
    {
        $otherUser = User::factory()->create();

        $draftListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::DRAFT,
            'is_published' => false,
        ]);

        $response = $this->actingAs($otherUser)
            ->patchJson("/api/v1/listings/{$draftListing->slug}/publish");

        $response->assertStatus(403);

        $this->assertDatabaseHas('listings', [
            'id' => $draftListing->id,
            'status' => ListingStatus::DRAFT->value,
            'is_published' => false,
        ]);
    }

    /**
     * Тест заборони зняття з публікації чужого оголошення
     */
    public function test_unpublish_forbidden_for_others_listing(): void
    {
        $otherUser = User::factory()->create();

        $publishedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
        ]);

        $response = $this->actingAs($otherUser)
            ->patchJson("/api/v1/listings/{$publishedListing->slug}/unpublish");

        $response->assertStatus(403);

        $this->assertDatabaseHas('listings', [
            'id' => $publishedListing->id,
            'status' => ListingStatus::PUBLISHED->value,
            'is_published' => true,
        ]);
    }

    /**
     * Тест кешування рекомендованих оголошень
     */
    public function test_featured_listings_are_cached(): void
    {
        // Очищаємо кеш перед тестом
        Cache::forget('featured_listings');

        // Створюємо рекомендоване оголошення
        $featuredListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => true,
            'is_published' => true,
        ]);

        // Перший запит має кешувати результати
        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        // Перевіряємо, що кеш існує
        $this->assertTrue(Cache::has('featured_listings'));

        // Створюємо нове рекомендоване оголошення
        $newFeaturedListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_featured' => true,
            'is_published' => true,
        ]);

        // Другий запит має використовувати кеш і не включати нове оголошення
        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        // Відповідь не повинна включати нове оголошення, тому що використовується кешовані дані
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($featuredListing->id, $listingIds);
        $this->assertNotContains($newFeaturedListing->id, $listingIds);

        // Очищаємо кеш
        Cache::forget('featured_listings');

        // Третій запит має оновити кеш і включити нове оголошення
        $response = $this->getJson('/api/v1/listings/featured');
        $response->assertStatus(200);

        // Тепер відповідь має включати обидва оголошення
        $listingIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($featuredListing->id, $listingIds);
        $this->assertContains($newFeaturedListing->id, $listingIds);
    }

    /**
     * Тест кешування схожих оголошень
     */
    public function test_similar_listings_are_cached(): void
    {
        // Створюємо схоже оголошення
        $similarListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => $this->listing->type,
            'price_per_night' => $this->listing->price_per_night->getAmount(),
        ]);

        // Очищаємо кеш перед тестом
        $cacheKey = 'similar_listings_'.$this->listing->id;
        Cache::forget($cacheKey);

        // Перший запит має кешувати результати
        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        // Перевіряємо, що кеш існує
        $this->assertTrue(Cache::has($cacheKey));

        // Створюємо нове схоже оголошення
        $newSimilarListing = Listing::factory()->create([
            'host_id' => $this->host->id,
            'status' => ListingStatus::PUBLISHED,
            'is_published' => true,
            'type' => $this->listing->type,
            'price_per_night' => $this->listing->price_per_night->getAmount(),
        ]);

        // Другий запит має використовувати кеш і не включати нове оголошення
        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        // Очищаємо кеш
        Cache::forget($cacheKey);

        // Третій запит має оновити кеш
        $response = $this->getJson("/api/v1/listings/{$this->listing->slug}/similar");
        $response->assertStatus(200);

        // Перевіряємо, що кеш був оновлений
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
            'is_published' => true,
        ]);

        Cache::flush();
    }
}
