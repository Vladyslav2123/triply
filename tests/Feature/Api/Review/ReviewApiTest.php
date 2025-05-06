<?php

namespace Tests\Feature\Api\Review;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private User $host;

    private Listing $listing;

    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guest = User::factory()->create();
        $this->host = User::factory()->create();
        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);
        $this->reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);
    }

    public function test_guest_can_create_review_for_completed_reservation(): void
    {
        $reviewData = [
            'overall_rating' => 4.5,
            'cleanliness_rating' => 5,
            'accuracy_rating' => 4,
            'checkin_rating' => 5,
            'communication_rating' => 4,
            'location_rating' => 5,
            'value_rating' => 4,
            'comment' => 'This was a great stay!',
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/reviews", $reviewData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'overall_rating',
                'cleanliness_rating',
                'accuracy_rating',
                'checkin_rating',
                'communication_rating',
                'location_rating',
                'value_rating',
                'comment',
                'reviewer',
                'reservation',
            ]);

        $this->assertDatabaseHas('reviews', [
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
            'overall_rating' => 4.5,
            'comment' => 'This was a great stay!',
        ]);
    }

    public function test_cannot_create_review_for_uncompleted_reservation(): void
    {
        $pendingReservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED, // Not completed
        ]);

        $reviewData = [
            'overall_rating' => 4.5,
            'comment' => 'This was a great stay!',
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$pendingReservation->id}/reviews", $reviewData);

        $response->assertStatus(403);
    }

    public function test_cannot_create_review_for_others_reservation(): void
    {
        $otherGuest = User::factory()->create();

        $reviewData = [
            'overall_rating' => 4.5,
            'comment' => 'This was a great stay!',
        ];

        $response = $this->actingAs($otherGuest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/reviews", $reviewData);

        $response->assertStatus(403);
    }

    public function test_can_get_reviews_for_listing(): void
    {
        // Create some reviews for the listing
        Review::factory()->count(3)->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'overall_rating',
                        'comment',
                        'reviewer',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_get_reviews_by_user(): void
    {
        // Create some reviews by the guest
        Review::factory()->count(3)->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        // Create reviews by another user
        $otherGuest = User::factory()->create();
        $otherReservation = Reservation::factory()->create([
            'guest_id' => $otherGuest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->count(2)->create([
            'reviewer_id' => $otherGuest->id,
            'reservation_id' => $otherReservation->id,
        ]);

        $response = $this->getJson("/api/v1/users/{$this->guest->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_get_single_review(): void
    {
        $review = Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->getJson("/api/v1/reviews/{$review->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'overall_rating',
                'cleanliness_rating',
                'accuracy_rating',
                'checkin_rating',
                'communication_rating',
                'location_rating',
                'value_rating',
                'comment',
                'reviewer',
                'reservation',
            ])
            ->assertJsonPath('id', $review->id);
    }

    public function test_user_can_update_own_review(): void
    {
        $review = Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
            'overall_rating' => 3.5,
            'comment' => 'Original comment',
        ]);

        $updateData = [
            'overall_rating' => 4.5,
            'comment' => 'Updated comment',
        ];

        $response = $this->actingAs($this->guest)
            ->putJson("/api/v1/reviews/{$review->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('overall_rating', 4.5)
            ->assertJsonPath('comment', 'Updated comment');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'overall_rating' => 4.5,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_user_cannot_update_others_review(): void
    {
        $review = Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        $otherUser = User::factory()->create();

        $updateData = [
            'overall_rating' => 4.5,
            'comment' => 'Updated comment',
        ];

        $response = $this->actingAs($otherUser)
            ->putJson("/api/v1/reviews/{$review->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_own_review(): void
    {
        $review = Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_user_cannot_delete_others_review(): void
    {
        $review = Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    public function test_can_get_average_ratings_for_listing(): void
    {
        // Create multiple reviews with different ratings
        Review::factory()->create([
            'reviewer_id' => $this->guest->id,
            'reservation_id' => $this->reservation->id,
            'overall_rating' => 5.0,
            'cleanliness_rating' => 5,
            'accuracy_rating' => 5,
        ]);

        $otherGuest = User::factory()->create();
        $otherReservation = Reservation::factory()->create([
            'guest_id' => $otherGuest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reviewer_id' => $otherGuest->id,
            'reservation_id' => $otherReservation->id,
            'overall_rating' => 4.0,
            'cleanliness_rating' => 4,
            'accuracy_rating' => 3,
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/ratings");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'overall_rating',
                'cleanliness_rating',
                'accuracy_rating',
                'checkin_rating',
                'communication_rating',
                'location_rating',
                'value_rating',
                'reviews_count',
            ])
            ->assertJson([
                'overall_rating' => 4.5,
                'cleanliness_rating' => 4.5,
                'accuracy_rating' => 4.0,
                'reviews_count' => 2,
            ]);
    }
}
