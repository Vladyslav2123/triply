<?php

namespace Tests\Feature\Commands;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeneratePaymentsCommandTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_generates_payments_for_confirmed_reservations(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $confirmedReservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $pendingReservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::PENDING,
        ]);

        Payment::query()->delete();

        $this->assertEquals(0, Payment::count());

        $this->artisan('app:generate-payments')
            ->expectsConfirmation('Do you want to continue?', 'yes')
            ->expectsOutput('Payments processed: 1')
            ->expectsOutput('Payments skipped: 0')
            ->assertSuccessful();

        $confirmedReservation->refresh();
        $pendingReservation->refresh();

        $this->assertNotNull($confirmedReservation->payment);

        $this->assertNull($pendingReservation->payment);
    }

    #[Test]
    public function it_generates_payments_with_specific_payment_method(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        Payment::query()->delete();

        $this->artisan('app:generate-payments --method=paypal')
            ->expectsConfirmation('Do you want to continue?', 'yes')
            ->expectsOutput('Payments processed: 1')
            ->assertSuccessful();

        $reservation->refresh();

        $this->assertNotNull($reservation->payment);
    }

    #[Test]
    public function it_skips_reservations_with_existing_payments(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        Payment::factory()
            ->forReservation($reservation)
            ->create();

        $this->assertNotNull($reservation->payment);
        $initialPaymentId = $reservation->payment->id;

        $this->artisan('app:generate-payments')
            ->assertSuccessful();

        $reservation->refresh();

        $this->assertEquals($initialPaymentId, $reservation->payment->id);
    }

    #[Test]
    public function it_forces_payment_recreation_when_force_option_is_used(): void
    {
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        Payment::factory()
            ->forReservation($reservation)
            ->create();

        $this->assertNotNull($reservation->payment);
        $initialPaymentId = $reservation->payment->id;

        $this->artisan('app:generate-payments --force')
            ->expectsConfirmation('Do you want to continue?', 'yes')
            ->expectsOutput('Payments processed: 1')
            ->expectsOutput('Payments skipped: 0')
            ->assertSuccessful();

        $reservation->refresh();

        $this->assertNotNull($reservation->payment);
        $this->assertNotEquals($initialPaymentId, $reservation->payment->id);
    }
}
