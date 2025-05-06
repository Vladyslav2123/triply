<?php

namespace Tests\Unit\Models;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $review->id);
    }

    #[Test]
    public function it_has_reservation_relationship(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertInstanceOf(Reservation::class, $review->reservation);
        $this->assertEquals($reservation->id, $review->reservation->id);
    }

    #[Test]
    public function it_has_reviewer_relationship(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertInstanceOf(User::class, $review->reviewer);
        $this->assertEquals($guest->id, $review->reviewer->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $review = new Review;
        $casts = $review->getCasts();

        $this->assertEquals('float', $casts['overall_rating']);
        $this->assertEquals('integer', $casts['cleanliness_rating']);
        $this->assertEquals('integer', $casts['accuracy_rating']);
        $this->assertEquals('integer', $casts['checkin_rating']);
        $this->assertEquals('integer', $casts['communication_rating']);
        $this->assertEquals('integer', $casts['location_rating']);
        $this->assertEquals('integer', $casts['value_rating']);
        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $array = $review->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_can_get_reservation_title(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
            'title' => 'Test Listing Title',
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertEquals('Test Listing Title', $review->reservation_title);
    }

    #[Test]
    public function it_can_scope_by_reservationable(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();

        $listing1 = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $listing2 = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing1->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing2->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review1 = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $guest->id,
        ]);

        $review2 = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $guest->id,
        ]);

        $scopedReviews = Review::forReservationable($listing1)->get();

        $this->assertCount(1, $scopedReviews);
        $this->assertEquals($review1->id, $scopedReviews->first()->id);
    }

    #[Test]
    public function it_can_scope_by_user(): void
    {
        $guest1 = User::factory()->create();
        $guest2 = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest1->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest2->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review1 = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $guest1->id,
        ]);

        $review2 = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $guest2->id,
        ]);

        $scopedReviews = Review::byUser($guest1)->get();

        $this->assertCount(1, $scopedReviews);
        $this->assertEquals($review1->id, $scopedReviews->first()->id);
    }

    #[Test]
    public function it_can_check_if_owned_by_user(): void
    {
        $guest = User::factory()->create();
        $otherUser = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertTrue($review->isOwnedBy($guest));
        $this->assertFalse($review->isOwnedBy($otherUser));
    }

    #[Test]
    public function it_can_store_ratings(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
            'overall_rating' => 4.5,
            'cleanliness_rating' => 5,
            'accuracy_rating' => 4,
            'checkin_rating' => 5,
            'communication_rating' => 4,
            'location_rating' => 5,
            'value_rating' => 4,
            'comment' => 'This was a great stay!',
        ]);

        $this->assertEquals(4.5, $review->overall_rating);
        $this->assertEquals(5, $review->cleanliness_rating);
        $this->assertEquals(4, $review->accuracy_rating);
        $this->assertEquals(5, $review->checkin_rating);
        $this->assertEquals(4, $review->communication_rating);
        $this->assertEquals(5, $review->location_rating);
        $this->assertEquals(4, $review->value_rating);
        $this->assertEquals('This was a great stay!', $review->comment);
    }
}
