<?php

namespace Tests\Feature\Api\ListingAvailability;

use App\Models\Listing;
use App\Models\ListingAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = User::factory()->create();
        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);
    }

    public function test_host_can_set_availability_for_listing(): void
    {
        $availabilityData = [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'price_override' => 15000, // 150.00 in cents
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/listings/{$this->listing->id}/availability", $availabilityData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'date',
                'is_available',
                'price_override',
            ]);

        $this->assertDatabaseHas('listing_availabilities', [
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
        ]);
    }

    public function test_non_host_cannot_set_availability_for_listing(): void
    {
        $user = User::factory()->create();

        $availabilityData = [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
        ];

        $response = $this->actingAs($user) // Not the host
            ->postJson("/api/v1/listings/{$this->listing->id}/availability", $availabilityData);

        $response->assertStatus(403);
    }

    public function test_host_can_update_availability(): void
    {
        $availability = ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'price_override' => 10000, // 100.00 in cents
        ]);

        $updateData = [
            'is_available' => false,
            'price_override' => 15000, // 150.00 in cents
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/listing-availabilities/{$availability->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('is_available', false)
            ->assertJsonPath('price_override.amount', 15000);

        $this->assertDatabaseHas('listing_availabilities', [
            'id' => $availability->id,
            'is_available' => false,
        ]);
    }

    public function test_non_host_cannot_update_availability(): void
    {
        $availability = ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
        ]);

        $user = User::factory()->create();

        $updateData = [
            'is_available' => false,
        ];

        $response = $this->actingAs($user) // Not the host
            ->putJson("/api/v1/listing-availabilities/{$availability->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_host_can_delete_availability(): void
    {
        $availability = ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
        ]);

        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/listing-availabilities/{$availability->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('listing_availabilities', [
            'id' => $availability->id,
        ]);
    }

    public function test_non_host_cannot_delete_availability(): void
    {
        $availability = ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user) // Not the host
            ->deleteJson("/api/v1/listing-availabilities/{$availability->id}");

        $response->assertStatus(403);
    }

    public function test_can_get_availability_for_listing(): void
    {
        // Create availabilities for different dates
        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
        ]);

        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'is_available' => false,
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/availability");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'is_available',
                        'price_override',
                    ],
                ],
            ]);
    }

    public function test_can_get_availability_for_date_range(): void
    {
        // Create availabilities for different dates
        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
        ]);

        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(10)->format('Y-m-d'),
            'is_available' => true,
        ]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/availability?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_host_can_set_bulk_availability(): void
    {
        $bulkData = [
            'dates' => [
                now()->addDays(1)->format('Y-m-d'),
                now()->addDays(2)->format('Y-m-d'),
                now()->addDays(3)->format('Y-m-d'),
            ],
            'is_available' => true,
            'price_override' => 12000, // 120.00 in cents
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/listings/{$this->listing->id}/bulk-availability", $bulkData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'count',
            ])
            ->assertJsonPath('count', 3);

        // Check that all dates were created
        foreach ($bulkData['dates'] as $date) {
            $this->assertDatabaseHas('listing_availabilities', [
                'listing_id' => $this->listing->id,
                'date' => $date,
                'is_available' => true,
            ]);
        }
    }

    public function test_host_can_set_availability_for_date_range(): void
    {
        $rangeData = [
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'price_override' => 12000, // 120.00 in cents
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/listings/{$this->listing->id}/range-availability", $rangeData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'count',
            ])
            ->assertJsonPath('count', 5);

        // Check that all dates in the range were created
        for ($i = 1; $i <= 5; $i++) {
            $date = now()->addDays($i)->format('Y-m-d');
            $this->assertDatabaseHas('listing_availabilities', [
                'listing_id' => $this->listing->id,
                'date' => $date,
                'is_available' => true,
            ]);
        }
    }

    public function test_can_check_if_listing_is_available_for_dates(): void
    {
        // Make some dates available
        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
        ]);

        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'is_available' => true,
        ]);

        // Make some dates unavailable
        ListingAvailability::factory()->create([
            'listing_id' => $this->listing->id,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'is_available' => false,
        ]);

        // Check available dates
        $checkData = [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(2)->format('Y-m-d'),
        ];

        $response = $this->postJson("/api/v1/listings/{$this->listing->id}/check-availability", $checkData);

        $response->assertStatus(200)
            ->assertJson([
                'is_available' => true,
            ]);

        // Check unavailable dates
        $checkData = [
            'check_in' => now()->addDays(1)->format('Y-m-d'),
            'check_out' => now()->addDays(3)->format('Y-m-d'),
        ];

        $response = $this->postJson("/api/v1/listings/{$this->listing->id}/check-availability", $checkData);

        $response->assertStatus(200)
            ->assertJson([
                'is_available' => false,
            ]);
    }
}
