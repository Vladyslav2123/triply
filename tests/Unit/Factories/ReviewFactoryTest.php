<?php

namespace Tests\Unit\Factories;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class ReviewFactoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_review_factory_creates_review_for_completed_reservation(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->forReservation($reservation)->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($reservation->id, $review->reservation_id);
        $this->assertEquals($user->id, $review->reviewer_id);
        $this->assertNotNull($review->overall_rating);
        $this->assertGreaterThanOrEqual(1, $review->cleanliness_rating);
        $this->assertLessThanOrEqual(5, $review->cleanliness_rating);
    }

    public function test_review_factory_throws_exception_for_non_completed_reservation(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::PENDING,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot create a review for a non-completed reservation.');

        Review::factory()->forReservation($reservation)->create();
    }

    public function test_review_factory_creates_positive_review(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->forReservation($reservation)->positive()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertGreaterThanOrEqual(4, $review->cleanliness_rating);
        $this->assertGreaterThanOrEqual(4, $review->overall_rating);
        $this->assertNotNull($review->comment);
    }

    public function test_review_factory_creates_negative_review(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->forReservation($reservation)->negative()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertLessThanOrEqual(2, $review->cleanliness_rating);
        $this->assertLessThanOrEqual(2, $review->overall_rating);
        $this->assertNotNull($review->comment);
    }

    public function test_review_factory_creates_neutral_review(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review = Review::factory()->forReservation($reservation)->neutral()->create();

        $this->assertInstanceOf(Review::class, $review);
        $this->assertGreaterThanOrEqual(2, $review->cleanliness_rating);
        $this->assertLessThanOrEqual(4, $review->cleanliness_rating);
        $this->assertNotNull($review->comment);
    }

    public function test_review_factory_throws_exception_for_reservation_with_existing_review(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $user->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This reservation already has a review.');

        Review::factory()->forReservation($reservation)->create();
    }
}
