<?php

namespace Tests\Feature\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class PaymentControllerTest extends ApiControllerTestCase
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
            'total_price' => 50000, // 500.00 in cents
        ]);
    }

    /**
     * Тест створення платежу
     */
    public function test_store_creates_payment(): void
    {
        $paymentData = [
            'amount' => 50000, // 500.00 in cents
            'payment_method' => PaymentMethod::CREDIT_CARD->value,
            'transaction_id' => 'txn_'.uniqid(),
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/payments", $paymentData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'amount',
                'payment_method',
                'transaction_id',
                'paid_at',
                'reservation',
            ]);

        $this->assertDatabaseHas('payments', [
            'reservation_id' => $this->reservation->id,
            'transaction_id' => $paymentData['transaction_id'],
        ]);

        // Перевіряємо, що статус бронювання оновлено
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => ReservationStatus::PAID,
        ]);
    }

    /**
     * Тест валідації при створенні платежу
     */
    public function test_store_validates_payment_data(): void
    {
        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/payments", [
                // Відсутні обов'язкові поля
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payment_method']);
    }

    /**
     * Тест заборони створення платежу для чужого бронювання
     */
    public function test_cannot_create_payment_for_others_reservation(): void
    {
        $otherGuest = User::factory()->create();

        $paymentData = [
            'amount' => 50000,
            'payment_method' => PaymentMethod::CREDIT_CARD->value,
        ];

        $response = $this->actingAs($otherGuest)
            ->postJson("/api/v1/reservations/{$this->reservation->id}/payments", $paymentData);

        $response->assertStatus(403);
    }

    /**
     * Тест заборони створення платежу для скасованого бронювання
     */
    public function test_cannot_create_payment_for_cancelled_reservation(): void
    {
        $cancelledReservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_GUEST,
        ]);

        $paymentData = [
            'amount' => 50000,
            'payment_method' => PaymentMethod::CREDIT_CARD->value,
        ];

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$cancelledReservation->id}/payments", $paymentData);

        $response->assertStatus(403);
    }

    /**
     * Тест отримання платежу для бронювання
     */
    public function test_get_payment_for_reservation(): void
    {
        $payment = Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/reservations/{$this->reservation->id}/payment");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'amount',
                'payment_method',
                'transaction_id',
                'paid_at',
            ])
            ->assertJsonPath('id', $payment->id);
    }

    /**
     * Тест дозволу хосту отримувати платіж для бронювання
     */
    public function test_host_can_get_payment_for_reservation(): void
    {
        $payment = Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->actingAs($this->host)
            ->getJson("/api/v1/reservations/{$this->reservation->id}/payment");

        $response->assertStatus(200)
            ->assertJsonPath('id', $payment->id);
    }

    /**
     * Тест заборони іншому користувачу отримувати платіж для бронювання
     */
    public function test_other_user_cannot_get_payment_for_reservation(): void
    {
        Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->getJson("/api/v1/reservations/{$this->reservation->id}/payment");

        $response->assertStatus(403);
    }

    /**
     * Тест отримання платежів користувача
     */
    public function test_get_user_payments(): void
    {
        // Створюємо кілька платежів для користувача
        $reservation1 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation1->id,
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation2->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/payments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'payment_method',
                        'transaction_id',
                        'paid_at',
                        'reservation',
                    ],
                ],
            ]);
    }

    /**
     * Тест отримання платежів для оголошень хоста
     */
    public function test_host_can_get_payments_for_own_listings(): void
    {
        // Створюємо кілька бронювань та платежів для оголошення хоста
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation1->id,
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation2->id,
        ]);

        $response = $this->actingAs($this->host)
            ->getJson('/api/v1/host/payments');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /**
     * Тест фільтрації платежів за датою
     */
    public function test_filter_payments_by_date_range(): void
    {
        // Створюємо платежі з різними датами
        $reservation1 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation1->id,
            'paid_at' => now()->subDays(10),
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation2->id,
            'paid_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/payments?from='.now()->subDays(5)->format('Y-m-d').'&to='.now()->format('Y-m-d'));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест фільтрації платежів за методом оплати
     */
    public function test_filter_payments_by_payment_method(): void
    {
        // Створюємо платежі з різними методами оплати
        $reservation1 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation1->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation2->id,
            'payment_method' => PaymentMethod::PAYPAL,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/payments?payment_method='.PaymentMethod::CREDIT_CARD->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест отримання деталей платежу
     */
    public function test_show_returns_payment_details(): void
    {
        $payment = Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'amount',
                'payment_method',
                'transaction_id',
                'paid_at',
                'reservation',
            ])
            ->assertJsonPath('id', $payment->id);
    }

    /**
     * Тест заборони перегляду чужого платежу
     */
    public function test_cannot_view_others_payment(): void
    {
        $otherGuest = User::factory()->create();
        $otherReservation = Reservation::factory()->create([
            'guest_id' => $otherGuest->id,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $otherReservation->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    /**
     * Тест створення квитанції для платежу
     */
    public function test_generate_receipt(): void
    {
        $payment = Payment::factory()->create([
            'reservation_id' => $this->reservation->id,
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/payments/{$payment->id}/receipt");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'receipt_url',
            ]);
    }

    /**
     * Тест отримання статистики платежів для хоста
     */
    public function test_host_can_get_payment_statistics(): void
    {
        // Створюємо кілька платежів для оголошень хоста
        $reservation1 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation1->id,
            'amount' => 50000, // 500.00
            'paid_at' => now()->subMonth(),
        ]);

        Payment::factory()->create([
            'reservation_id' => $reservation2->id,
            'amount' => 75000, // 750.00
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($this->host)
            ->getJson('/api/v1/host/payment-statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_earnings',
                'monthly_earnings',
                'payment_count',
                'average_payment',
                'monthly_breakdown',
            ]);
    }

    /**
     * Тест заборони отримання статистики платежів для не-хоста
     */
    public function test_non_host_cannot_get_payment_statistics(): void
    {
        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/host/payment-statistics');

        $response->assertStatus(403);
    }
}
