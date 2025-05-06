<?php

namespace Tests\Feature\Api\Reservation;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $guest;

    private User $host;

    private Listing $listing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->guest = User::factory()->create();
        $this->host = User::factory()->create();
        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);
    }

    public function test_guest_can_create_reservation(): void
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

    public function test_unauthenticated_user_cannot_create_reservation(): void
    {
        $reservationData = [
            'check_in' => now()->addDays(5)->format('Y-m-d'),
            'check_out' => now()->addDays(10)->format('Y-m-d'),
            'guests_count' => 2,
        ];

        $response = $this->postJson("/api/v1/listings/{$this->listing->id}/reservations", $reservationData);

        $response->assertStatus(401);
    }

    public function test_guest_can_get_own_reservations(): void
    {
        // Create some reservations for the guest
        Reservation::factory()->count(3)->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        // Create reservations for another guest
        Reservation::factory()->count(2)->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson('/api/v1/reservations');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_host_can_get_reservations_for_own_listing(): void
    {
        // Create some reservations for the listing
        Reservation::factory()->count(3)->create([
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->host)
            ->getJson("/api/v1/listings/{$this->listing->id}/reservations");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_guest_can_get_single_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/reservations/{$reservation->id}");

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
            ->assertJsonPath('id', $reservation->id);
    }

    public function test_guest_cannot_get_others_reservation(): void
    {
        $otherGuest = User::factory()->create();
        $reservation = Reservation::factory()->create([
            'guest_id' => $otherGuest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->guest)
            ->getJson("/api/v1/reservations/{$reservation->id}");

        $response->assertStatus(403);
    }

    public function test_guest_can_cancel_own_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($this->guest)
            ->postJson("/api/v1/reservations/{$reservation->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CANCELLED_BY_GUEST->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CANCELLED_BY_GUEST,
        ]);
    }

    public function test_host_can_cancel_reservation_for_own_listing(): void
    {
        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$reservation->id}/cancel-by-host");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CANCELLED_BY_HOST->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CANCELLED_BY_HOST,
        ]);
    }

    public function test_host_cannot_cancel_reservation_for_others_listing(): void
    {
        $otherHost = User::factory()->create();
        $otherListing = Listing::factory()->create([
            'host_id' => $otherHost->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $otherListing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$reservation->id}/cancel-by-host");

        $response->assertStatus(403);
    }

    public function test_host_can_confirm_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $this->listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::PENDING,
        ]);

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/reservations/{$reservation->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonPath('status', ReservationStatus::CONFIRMED->value);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => ReservationStatus::CONFIRMED,
        ]);
    }

    public function test_can_filter_reservations_by_status(): void
    {
        // Create reservations with different statuses
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
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_reservations_by_date_range(): void
    {
        // Create reservations with different date ranges
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
}
