<?php

namespace Tests\Unit\Models;

use App\Models\Listing;
use App\Models\ListingAvailability;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListingAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $availability->id);
    }

    #[Test]
    public function it_has_listing_relationship(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
        ]);

        $this->assertInstanceOf(Listing::class, $availability->listing);
        $this->assertEquals($listing->id, $availability->listing->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $availability = new ListingAvailability;
        $casts = $availability->getCasts();

        $this->assertEquals('datetime:Y-m-d', $casts['date']);
        $this->assertEquals('boolean', $casts['is_available']);
    }

    #[Test]
    public function it_can_be_marked_as_available(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'is_available' => true,
        ]);

        $this->assertTrue($availability->is_available);
    }

    #[Test]
    public function it_can_be_marked_as_unavailable(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'is_available' => false,
        ]);

        $this->assertFalse($availability->is_available);
    }

    #[Test]
    public function it_can_have_price_override(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'price_override' => 15000, // 150.00 in cents
        ]);

        $this->assertInstanceOf(Money::class, $availability->price_override);
        $this->assertEquals(15000, $availability->price_override->getAmount());
        $this->assertEquals('USD', $availability->price_override->getCurrency()->getCode());
    }

    #[Test]
    public function it_can_filter_by_date(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $date = now()->format('Y-m-d');

        $availability = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $date,
        ]);

        $filtered = ListingAvailability::query()
            ->where('date', $date)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($availability->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_listing(): void
    {
        $host = User::factory()->create();
        $listing1 = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $listing2 = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availability1 = ListingAvailability::factory()->create([
            'listing_id' => $listing1->id,
        ]);

        $availability2 = ListingAvailability::factory()->create([
            'listing_id' => $listing2->id,
        ]);

        $filtered = ListingAvailability::query()
            ->where('listing_id', $listing1->id)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($availability1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_availability(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $availabilityTrue = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'is_available' => true,
        ]);

        $availabilityFalse = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'is_available' => false,
        ]);

        $filteredAvailable = ListingAvailability::query()
            ->where('is_available', true)
            ->get();

        $this->assertCount(1, $filteredAvailable);
        $this->assertEquals($availabilityTrue->id, $filteredAvailable->first()->id);

        $filteredUnavailable = ListingAvailability::query()
            ->where('is_available', false)
            ->get();

        $this->assertCount(1, $filteredUnavailable);
        $this->assertEquals($availabilityFalse->id, $filteredUnavailable->first()->id);
    }

    #[Test]
    public function it_can_filter_by_date_range(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $now = now();

        $availability1 = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $now->copy()->addDays(1),
        ]);

        $availability2 = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $now->copy()->addDays(2),
        ]);

        $availability3 = ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $now->copy()->addDays(10),
        ]);

        $filtered = ListingAvailability::query()
            ->where('date', '>=', $now->copy()->addDays(1))
            ->where('date', '<=', $now->copy()->addDays(5))
            ->get();

        $this->assertCount(2, $filtered);
        $this->assertTrue($filtered->contains('id', $availability1->id));
        $this->assertTrue($filtered->contains('id', $availability2->id));
        $this->assertFalse($filtered->contains('id', $availability3->id));
    }

    #[Test]
    public function it_enforces_unique_date_per_listing(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $date = now()->format('Y-m-d');

        // Create first availability
        ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $date,
        ]);

        // Try to create another availability for the same date and listing
        // This should throw an exception due to the unique constraint
        ListingAvailability::factory()->create([
            'listing_id' => $listing->id,
            'date' => $date,
        ]);
    }
}
