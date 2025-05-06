<?php

namespace Tests\Feature\Controllers;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class ReservationControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $guest;

    private User $host;

    private Listing $listing;

    private Reservation $reservation;

    protected function setUp(): void
    {
        parent::setUp();

        // Створюємо гостя, хоста, оголошення та бронювання
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
            'status' => ReservationStatus::CONFIRMED,
        ]);
    }

    /**
     * Тест створення бронювання
     */
    public function test_store_creates_reservation(): void
    {
        $reservationData = [
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests_count' => 2,
            'total_price' => 50000, // 500.00 in cents
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/listings/{$this->listing->id}/reservations", $reservationData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'check_in',
                'check_out',
                'guests_count',
                'total_price',
                'status',
                'guest',
                'listing',
            ]);

        $this->assertDatabaseHas('reservations', [
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'guests_count' => 2,
        ]);
    }

    /**
     * Тест валідації при створенні бронювання
     */
    public function test_store_validates_reservation_data(): void
    {
        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/listings/{$this->listing->id}/reservations", [
                // Відсутні обов'язкові поля
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in', 'check_out', 'guests_count']);
    }

    /**
     * Тест отримання бронювань користувача
     */
    public function test_index_returns_user_reservations(): void
    {
        // Створюємо додаткові бронювання для гостя
        Reservation::factory()->count(2)->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data') // 2 нових + 1 створений в setUp
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'check_in',
                        'check_out',
                        'guests_count',
                        'total_price',
                        'status',
                        'listing',
                    ],
                ],
            ]);
    }

    /**
     * Тест отримання бронювань для оголошення хоста
     */
    public function test_get_listing_reservations(): void
    {
        // Створюємо додаткові бронювання для оголошення
        Reservation::factory()->count(2)->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->host)
            ->getJson("/api/v1/listings/{$this->listing->id}/reservations");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // 2 нових + 1 створений в setUp
    }

    /**
     * Тест отримання деталей бронювання
     */
    public function test_show_returns_reservation_details(): void
    {
        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/reservations/{$this->reservation->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'check_in',
                'check_out',
                'guests_count',
                'total_price',
                'status',
                'guest',
                'listing',
            ])
            ->assertJsonPath('id', $this->reservation->id);
    }

    /**
     * Тест заборони перегляду чужого бронювання
     */
    public function test_show_forbidden_for_others_reservation(): void
    {
        $otherGuest = User::factory()->create();

        $response = $this->actingAs($otherGuest)
            ->getJson("/api/v1/reservations/{$this->reservation->id}");

        $response->assertStatus(403);
    }

    /**
     * Тест скасування бронювання гостем
     */
    public function test_guest_can_cancel_reservation(): void
    {
        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CANCELLED_BY_GUEST->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => ReservationStatus::CANCELLED_BY_GUEST,
        ]);
    }

    /**
     * Тест скасування бронювання хостом
     */
    public function test_host_can_cancel_reservation(): void
    {
        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/cancel-by-host");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CANCELLED_BY_HOST->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => ReservationStatus::CANCELLED_BY_HOST,
        ]);
    }

    /**
     * Тест заборони скасування чужого бронювання хостом
     */
    public function test_host_cannot_cancel_others_listing_reservation(): void
    {
        $otherHost = User::factory()->create([
            'role' => 'host',
        ]);

        $response = $this->actingAs($otherHost)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/cancel-by-host");

        $response->assertStatus(403);
    }

    /**
     * Тест підтвердження бронювання хостом
     */
    public function test_host_can_confirm_reservation(): void
    {
        // Створюємо бронювання зі статусом PENDING
        $pendingReservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$pendingReservation->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CONFIRMED->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $pendingReservation->id,
            'status' => ReservationStatus::CONFIRMED,
        ]);
    }

    /**
     * Тест фільтрації бронювань за статусом
     */
    public function test_index_filters_reservations_by_status(): void
    {
        // Створюємо бронювання з різними статусами
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_GUEST,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations?status='.ReservationStatus::CONFIRMED->value);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 1 новий + 1 створений в setUp
    }

    /**
     * Тест фільтрації бронювань за датами
     */
    public function test_index_filters_reservations_by_date_range(): void
    {
        // Створюємо бронювання з різними датами
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(10),
        ]);

        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'check_in' => now()->addDays(20),
            'check_out' => now()->addDays(25),
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations?from='.now()->addDays(1)->format('Y-m-d').'&to='.now()->addDays(15)->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест перевірки доступності дат для бронювання
     */
    public function test_check_availability(): void
    {
        $checkData = [
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
        ];

        $response = $this->getJson("/api/v1/listings/{$this->listing->id}/check-availability?".http_build_query($checkData));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'is_available',
                'unavailable_dates',
            ]);
    }

    /**
     * Тест отримання активних бронювань
     */
    public function test_get_active_reservations(): void
    {
        // Створюємо активні бронювання
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
            'check_in' => now()->subDays(2),
            'check_out' => now()->addDays(3),
        ]);

        // Створюємо неактивні бронювання
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_GUEST,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations/active');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 1 новий активний + 1 створений в setUp
    }

    /**
     * Тест отримання майбутніх бронювань
     */
    public function test_get_upcoming_reservations(): void
    {
        // Створюємо майбутні бронювання
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
            'check_in' => now()->addDays(5),
            'check_out' => now()->addDays(10),
        ]);

        // Створюємо минулі бронювання
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
            'check_in' => now()->subDays(10),
            'check_out' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations/upcoming');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data'); // 1 новий майбутній + 1 створений в setUp
    }

    /**
     * Тест отримання історії бронювань
     */
    public function test_get_history_reservations(): void
    {
        // Створюємо завершені бронювання
        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED,
            'check_in' => now()->subDays(10),
            'check_out' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations/history');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест позначення бронювання як завершеного
     */
    public function test_mark_reservation_as_completed(): void
    {
        // Створюємо бронювання, яке має бути завершено
        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
            'check_in' => now()->subDays(5),
            'check_out' => now()->subDays(1), // Вже виселився
        ]);

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$reservation->id}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::COMPLETED->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::COMPLETED,
        ]);
    }
}
