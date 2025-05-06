<?php

namespace Tests\Unit\Models;

use App\Enums\PaymentMethod;
use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $payment->id);
    }

    #[Test]
    public function it_has_reservation_relationship(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
        ]);

        $this->assertInstanceOf(Reservation::class, $payment->reservation);
        $this->assertEquals($reservation->id, $payment->reservation->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $payment = new Payment;
        $casts = $payment->getCasts();

        $this->assertEquals('datetime:Y-m-d H:i', $casts['paid_at']);
        $this->assertEquals(PaymentMethod::class, $casts['payment_method']);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
        ]);

        $array = $payment->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
        $this->assertArrayNotHasKey('deleted_at', $array);
    }

    #[Test]
    public function it_can_handle_money_values(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
            'total_price' => 10000,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'amount' => 10000,
        ]);

        $this->assertInstanceOf(Money::class, $payment->amount);
        $this->assertEquals(10000, $payment->amount->getAmount());
        $this->assertEquals('USD', $payment->amount->getCurrency()->getCode());
    }

    #[Test]
    public function it_can_store_payment_method(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        $this->assertInstanceOf(PaymentMethod::class, $payment->payment_method);
        $this->assertEquals(PaymentMethod::CREDIT_CARD, $payment->payment_method);
    }

    #[Test]
    public function it_can_store_paid_at_timestamp(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $paidAt = now();

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
            'paid_at' => $paidAt,
        ]);

        $this->assertEquals($paidAt->format('Y-m-d H:i'), $payment->paid_at->format('Y-m-d H:i'));
    }

    #[Test]
    public function it_can_filter_by_payment_method(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $payment1 = Payment::factory()->create([
            'reservation_id' => $reservation1->id,
            'payment_method' => PaymentMethod::CREDIT_CARD,
        ]);

        $payment2 = Payment::factory()->create([
            'reservation_id' => $reservation2->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER,
        ]);

        $creditCardPayments = Payment::query()
            ->where('payment_method', PaymentMethod::CREDIT_CARD)
            ->get();

        $this->assertCount(1, $creditCardPayments);
        $this->assertEquals($payment1->id, $creditCardPayments->first()->id);
    }

    #[Test]
    public function it_can_filter_by_paid_at(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $yesterday = now()->subDay();
        $today = now();

        $payment1 = Payment::factory()->create([
            'reservation_id' => $reservation1->id,
            'paid_at' => $yesterday,
        ]);

        $payment2 = Payment::factory()->create([
            'reservation_id' => $reservation2->id,
            'paid_at' => $today,
        ]);

        $yesterdayPayments = Payment::query()
            ->whereDate('paid_at', $yesterday->format('Y-m-d'))
            ->get();

        $this->assertCount(1, $yesterdayPayments);
        $this->assertEquals($payment1->id, $yesterdayPayments->first()->id);
    }
}
