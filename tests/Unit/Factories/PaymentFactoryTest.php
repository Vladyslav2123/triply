<?php

namespace Tests\Unit\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentFactoryTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_creates_payment_with_default_values(): void
    {
        // Create a reservation first
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
            'total_price' => 5000,
        ]);

        // Create a payment using the factory
        $payment = Payment::factory()->create();

        // Assert payment was created
        $this->assertNotNull($payment);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
        ]);
    }

    #[Test]
    public function it_creates_payment_for_specific_reservation(): void
    {
        // Create a reservation first
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
            'total_price' => 5000,
        ]);

        // Create a payment for this reservation
        $payment = Payment::factory()
            ->forReservation($reservation)
            ->create();

        // Assert payment was created for the correct reservation
        $this->assertNotNull($payment);
        $this->assertEquals($reservation->id, $payment->reservation_id);
        $this->assertEquals($reservation->total_price->getAmount(), $payment->amount->getAmount());
    }

    #[Test]
    public function it_creates_payment_with_specific_payment_method(): void
    {
        // Create a reservation first
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        // Create a payment with a specific payment method
        $payment = Payment::factory()
            ->forReservation($reservation)
            ->withPaymentMethod(PaymentMethod::PAYPAL)
            ->create();

        // Assert payment was created with the correct payment method
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentMethod::PAYPAL, $payment->payment_method);
    }

    #[Test]
    public function it_creates_payment_with_specific_status(): void
    {
        // Create a reservation first
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        // Create a payment with a specific status
        $payment = Payment::factory()
            ->forReservation($reservation)
            ->withStatus(PaymentStatus::PROCESSING)
            ->create();

        // Assert payment was created with the correct status
        $this->assertNotNull($payment);
        $this->assertEquals(PaymentStatus::PROCESSING, $payment->status);
    }

    #[Test]
    public function it_creates_payment_with_specific_paid_date(): void
    {
        // Create a reservation first
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $paidAt = new \DateTime('2023-01-01');

        // Create a payment with a specific paid date
        $payment = Payment::factory()
            ->forReservation($reservation)
            ->paidAt($paidAt)
            ->create();

        // Assert payment was created with the correct paid date
        $this->assertNotNull($payment);
        $this->assertEquals($paidAt->format('Y-m-d'), $payment->paid_at->format('Y-m-d'));
    }

    #[Test]
    public function reservation_factory_creates_payment_automatically_for_confirmed_reservations(): void
    {
        // Create a user and host
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        // Create a confirmed reservation
        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        // Assert a payment was automatically created
        $this->assertNotNull($reservation->payment);
        $this->assertEquals($reservation->total_price->getAmount(), $reservation->payment->amount->getAmount());
    }

    #[Test]
    public function reservation_factory_creates_payment_automatically_for_completed_reservations(): void
    {
        // Create a user and host
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        // Create a completed reservation
        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        // Assert a payment was automatically created
        $this->assertNotNull($reservation->payment);
        $this->assertEquals($reservation->total_price->getAmount(), $reservation->payment->amount->getAmount());
    }

    #[Test]
    public function reservation_factory_does_not_create_payment_for_pending_reservations(): void
    {
        // Create a user and host
        $user = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $host->id]);

        // Create a pending reservation
        $reservation = Reservation::factory()->create([
            'guest_id' => $user->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::PENDING,
        ]);

        // Assert no payment was created
        $this->assertNull($reservation->payment);
    }
}
