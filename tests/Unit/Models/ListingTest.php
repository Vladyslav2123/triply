<?php

namespace Tests\Unit\Models;

use App\Casts\AcceptGuestCast;
use App\Casts\AccessibilityFeatureCast;
use App\Casts\AvailabilitySettingCast;
use App\Casts\DescriptionCast;
use App\Casts\GuestSafetyCast;
use App\Casts\HouseRuleCast;
use App\Casts\LocationCast;
use App\Casts\RoomRuleCast;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use App\ValueObjects\Listing\HouseRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory(['host_id' => $user->id])->create();

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $listing->id);
    }

    #[Test]
    public function it_has_correct_relationships(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);
        User::factory()->count(3)->create();

        $favorite = Favorite::factory()
            ->forListing($listing)
            ->create();

        $listing->refresh();
        $listing->load('favorites');

        $this->assertNotEmpty($listing->favorites, 'Favorites relationship should not be empty');
        $this->assertInstanceOf(Favorite::class, $listing->favorites->first());
        $this->assertTrue($listing->favorites->contains($favorite));
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $listing = new Listing;
        $casts = $listing->getCasts();

        $this->assertEquals('array', $casts['amenities']);
        $this->assertEquals(HouseRuleCast::class, $casts['house_rules']);
        $this->assertEquals(AccessibilityFeatureCast::class, $casts['accessibility_features']);
        $this->assertEquals(ListingStatus::class, $casts['status']);
        $this->assertEquals(ListingType::class, $casts['listing_type']);
        $this->assertEquals('decimal:2', $casts['rating']);
        $this->assertEquals('array', $casts['seo']);
        $this->assertEquals(DescriptionCast::class, $casts['description']);
        $this->assertEquals(LocationCast::class, $casts['location']);
        $this->assertEquals(AvailabilitySettingCast::class, $casts['availability_settings']);
        $this->assertEquals(RoomRuleCast::class, $casts['rooms_rules']);
        $this->assertEquals(AcceptGuestCast::class, $casts['accept_guests']);
        $this->assertEquals(GuestSafetyCast::class, $casts['guest_safety']);
    }

    #[Test]
    public function it_can_filter_by_price_range(): void
    {
        $user = User::factory()->create();

        Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => money(10000),
        ]);

        Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => money(20000),
        ]);

        Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => money(30000),
        ]);

        $filteredListings = Listing::query()
            ->filterByPrice(15000, 25000)
            ->get();

        $this->assertCount(1, $filteredListings);
        $this->assertTrue(
            money(20000)->equals($filteredListings->first()->price_per_night),
            'Expected price should be $200.00'
        );
    }

    #[Test]
    public function it_can_filter_by_guests(): void
    {
        $user = User::factory()->create();

        Listing::factory()->create([
            'host_id' => $user->id,
            'house_rules' => new HouseRule(
                petsAllowed: false,
                eventsAllowed: false,
                smokingAllowed: false,
                quietHours: false,
                commercialPhotographyAllowed: false,
                numberOfGuests: 2,
                additionalRules: '',
            ),
        ]);

        Listing::factory()->create([
            'host_id' => $user->id,
            'house_rules' => new HouseRule(
                petsAllowed: false,
                eventsAllowed: false,
                smokingAllowed: false,
                quietHours: false,
                commercialPhotographyAllowed: false,
                numberOfGuests: 4,
                additionalRules: '',
            ),
        ]);

        $filteredListings = Listing::query()
            ->filterByGuests(3)
            ->get();

        $this->assertCount(1, $filteredListings);
        $this->assertEquals(4, $filteredListings->first()->house_rules->numberOfGuests);
    }

    #[Test]
    public function it_can_filter_by_amenities(): void
    {
        $user = User::factory()->create();

        Listing::factory()->create([
            'host_id' => $user->id,
            'amenities' => ['wifi', 'pool'],
        ]);

        Listing::factory()->create([
            'host_id' => $user->id,
            'amenities' => ['wifi'],
        ]);

        $filteredListings = Listing::query()
            ->filterByAmenities(['wifi', 'pool'])
            ->get();

        $this->assertCount(1, $filteredListings);
        $this->assertEquals(['wifi', 'pool'], $filteredListings->first()->amenities);
    }

    #[Test]
    public function it_can_sort_by_price(): void
    {
        $user = User::factory()->create();

        $listing1 = Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => 300,
        ]);

        $listing2 = Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => 100,
        ]);

        $listing3 = Listing::factory()->create([
            'host_id' => $user->id,
            'price_per_night' => 200,
        ]);

        $sortedListings = Listing::query()
            ->orderBy('price_per_night', 'asc')
            ->get();

        $this->assertEquals($listing2->id, $sortedListings->first()->id);
        $this->assertEquals($listing1->id, $sortedListings->last()->id);
    }

    #[Test]
    public function it_can_filter_by_rating(): void
    {
        $user = User::factory()->create();

        Listing::factory()->create([
            'host_id' => $user->id,
            'rating' => 5.0,
        ]);

        Listing::factory()->create([
            'host_id' => $user->id,
            'rating' => 3.0,
        ]);

        $filteredListings = Listing::query()
            ->where('rating', '>=', 4.0)
            ->get();

        $this->assertCount(1, $filteredListings);
        $this->assertEquals(5.0, $filteredListings->first()->rating);
    }
}
