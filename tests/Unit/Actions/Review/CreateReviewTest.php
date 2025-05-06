<?php

namespace Tests\Unit\Actions\Review;

use App\Actions\Review\CreateReview;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateReviewTest extends TestCase
{
    use RefreshDatabase;

    private CreateReview $createReview;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createReview = new CreateReview;
    }

    public function test_it_creates_a_review(): void
    {
        // Arrange
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
        ]);

        $data = [
            'reservation_id' => $reservation->id,
            'reviewer_id' => $user->id,
            'cleanliness_rating' => 5,
            'accuracy_rating' => 4,
            'checkin_rating' => 5,
            'communication_rating' => 4,
            'location_rating' => 5,
            'value_rating' => 4,
            'comment' => 'Great place to stay! Very clean and comfortable.',
        ];

        // Act
        $review = $this->createReview->execute($data);

        // Assert
        $this->assertInstanceOf(Review::class, $review);
        $this->assertEquals($reservation->id, $review->reservation_id);
        $this->assertEquals($user->id, $review->reviewer_id);
        $this->assertEquals(5, $review->cleanliness_rating);
        $this->assertEquals(4, $review->accuracy_rating);
        $this->assertEquals(5, $review->checkin_rating);
        $this->assertEquals(4, $review->communication_rating);
        $this->assertEquals(5, $review->location_rating);
        $this->assertEquals(4, $review->value_rating);
        $this->assertEquals(4.5, $review->overall_rating);
        $this->assertEquals('Great place to stay! Very clean and comfortable.', $review->comment);
    }

    public function test_it_calculates_overall_rating_correctly(): void
    {
        // Arrange
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
        ]);

        $data = [
            'reservation_id' => $reservation->id,
            'reviewer_id' => $user->id,
            'cleanliness_rating' => 3,
            'accuracy_rating' => 4,
            'checkin_rating' => 5,
            'communication_rating' => 2,
            'location_rating' => 3,
            'value_rating' => 4,
            'comment' => 'Average experience.',
        ];

        // Act
        $review = $this->createReview->execute($data);

        // Assert
        $this->assertEquals(3.5, $review->overall_rating);
    }
}
