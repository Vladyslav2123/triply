<?php

namespace Tests\Unit\Seeders;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\PaymentSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentSeederTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_creates_payments_for_confirmed_and_completed_reservations_without_payments(): void
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

        $completedReservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        $pendingReservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::PENDING,
        ]);

        Payment::query()->delete();

        $this->assertEquals(0, Payment::count());

        $this->seed(PaymentSeeder::class);

        $confirmedReservation->refresh();
        $completedReservation->refresh();
        $pendingReservation->refresh();

        $this->assertNotNull($confirmedReservation->payment);
        $this->assertNotNull($completedReservation->payment);

        $this->assertNull($pendingReservation->payment);

        $this->assertEquals(2, Payment::count());
    }

    #[Test]
    public function it_does_not_create_duplicate_payments(): void
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

        $this->seed(PaymentSeeder::class);

        $reservation->refresh();

        $this->assertEquals(1, $reservation->payment()->count());
        $this->assertEquals($initialPaymentId, $reservation->payment->id);
    }
}
