<?php

namespace Tests\Feature\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class ReviewControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $guest;

    private User $host;

    private Listing $listing;

    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Створюємо гостя, хоста, оголошення та завершене бронювання
        $this->guest = User::factory()->create([
            'role' => 'guest',
        ]);

        $this->host = User::factory()->create([
            'role' => 'host',
        ]);

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

    /**
     * Тест створення відгуку
     */
    public function test_store_creates_review(): void
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

    /**
     * Тест заборони створення відгуку для незавершеного бронювання
     */
    public function test_cannot_create_review_for_uncompleted_reservation(): void
    {
        $pendingReservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED, // Не завершено
        ]);

        $reviewData = [
            'overall_rating' => 4.5,
            'comment' => 'This was a great stay!',
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$pendingReservation->id}/reviews", $reviewData);

        $response->assertStatus(403);
    }

    /**
     * Тест заборони створення відгуку для чужого бронювання
     */
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

    /**
     * Тест валідації при створенні відгуку
     */
    public function test_store_validates_review_data(): void
    {
        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/reviews", [
                // Відсутні обов'язкові поля
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['overall_rating']);
    }

    /**
     * Тест отримання відгуків для оголошення
     */
    public function test_get_listing_reviews(): void
    {
        // Створюємо відгуки для оголошення
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review1 = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $reservation1->guest_id,
        ]);

        $review2 = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $reservation2->guest_id,
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
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

    /**
     * Тест отримання відгуків користувача
     */
    public function test_get_user_reviews(): void
    {
        // Створюємо відгуки від користувача
        $reservation1 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $review1 = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $this->guest->id,
        ]);

        $review2 = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $this->guest->id,
        ]);

        $response = $this->getJson("/api/v1/users/{$this->guest->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Тест отримання деталей відгуку
     */
    public function test_show_returns_review_details(): void
    {
        $review = Review::factory()->create([
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
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

    /**
     * Тест оновлення відгуку
     */
    public function test_update_modifies_review(): void
    {
        $review = Review::factory()->create([
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
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

    /**
     * Тест заборони оновлення чужого відгуку
     */
    public function test_cannot_update_others_review(): void
    {
        $review = Review::factory()->create([
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
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

    /**
     * Тест видалення відгуку
     */
    public function test_destroy_removes_review(): void
    {
        $review = Review::factory()->create([
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * Тест заборони видалення чужого відгуку
     */
    public function test_cannot_delete_others_review(): void
    {
        $review = Review::factory()->create([
            'reservation_id' => $this->reservation->id,
            'reviewer_id' => $this->guest->id,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    /**
     * Тест отримання середніх рейтингів для оголошення
     */
    public function test_get_listing_ratings(): void
    {
        // Створюємо відгуки з різними рейтингами
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $reservation1->guest_id,
            'overall_rating' => 5.0,
            'cleanliness_rating' => 5,
            'accuracy_rating' => 5,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $reservation2->guest_id,
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

    /**
     * Тест фільтрації відгуків за рейтингом
     */
    public function test_filter_reviews_by_rating(): void
    {
        // Створюємо відгуки з різними рейтингами
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $reservation1->guest_id,
            'overall_rating' => 5.0,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $reservation2->guest_id,
            'overall_rating' => 3.0,
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews?min_rating=4");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест сортування відгуків за датою
     */
    public function test_sort_reviews_by_date(): void
    {
        // Створюємо відгуки з різними датами
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $oldReview = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $reservation1->guest_id,
            'created_at' => now()->subDays(10),
        ]);

        $newReview = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $reservation2->guest_id,
            'created_at' => now(),
        ]);

        // Сортування за зростанням дати
        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews?sort=oldest");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $oldReview->id);

        // Сортування за спаданням дати
        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews?sort=newest");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $newReview->id);
    }

    /**
     * Тест отримання відгуків з коментарями
     */
    public function test_filter_reviews_with_comments(): void
    {
        // Створюємо відгуки з коментарями та без
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
        ]);

        $reviewWithComment = Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $reservation1->guest_id,
            'comment' => 'This is a detailed comment',
        ]);

        $reviewWithoutComment = Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $reservation2->guest_id,
            'comment' => '',
        ]);

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/reviews?has_comments=true");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reviewWithComment->id);
    }
}
