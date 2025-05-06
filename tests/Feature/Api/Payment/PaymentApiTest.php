<?php

namespace Tests\Feature\Api\Payment;

use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentApiTest extends TestCase
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
            'status' => ReservationStatus::CONFIRMED,
            'total_price' => 50000, // 500.00 in cents
        ]);
    }

    public function test_guest_can_create_payment_for_reservation(): void
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

        // Check that reservation status is updated
        $this->assertDatabaseHas('reservations', [
            'id' => $this->reservation->id,
            'status' => ReservationStatus::PAID,
        ]);
    }

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

    public function test_guest_can_get_payment_for_reservation(): void
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

    public function test_guest_can_get_own_payments(): void
    {
        // Create multiple payments for the guest
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

    public function test_host_can_get_payments_for_own_listings(): void
    {
        // Create multiple reservations and payments for the host's listing
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

    public function test_can_filter_payments_by_date_range(): void
    {
        // Create payments with different dates
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

    public function test_can_filter_payments_by_payment_method(): void
    {
        // Create payments with different methods
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
}
