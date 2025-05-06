<?php

namespace Tests\Unit\Models;

use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReservationTest extends TestCase
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
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $reservation->id);
    }

    #[Test]
    public function it_has_guest_relationship(): void
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
        ]);

        $this->assertInstanceOf(User::class, $reservation->guest);
        $this->assertEquals($guest->id, $reservation->guest->id);
    }

    #[Test]
    public function it_has_reservationable_relationship_for_listing(): void
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
        ]);

        $this->assertInstanceOf(Listing::class, $reservation->reservationable);
        $this->assertEquals($listing->id, $reservation->reservationable->id);
    }

    #[Test]
    public function it_has_reservationable_relationship_for_experience(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $experience->id,
            'reservationable_type' => 'experience',
        ]);

        $this->assertInstanceOf(Experience::class, $reservation->reservationable);
        $this->assertEquals($experience->id, $reservation->reservationable->id);
    }

    #[Test]
    public function it_has_review_relationship(): void
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
            'status' => ReservationStatus::COMPLETED->value,
        ]);

        $review = Review::factory()->create([
            'reservation_id' => $reservation->id,
            'reviewer_id' => $guest->id,
        ]);

        $this->assertInstanceOf(Review::class, $reservation->review);
        $this->assertEquals($review->id, $reservation->review->id);
    }

    #[Test]
    public function it_has_payment_relationship(): void
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
            'status' => ReservationStatus::CONFIRMED->value,
        ]);

        $payment = Payment::factory()->create([
            'reservation_id' => $reservation->id,
        ]);

        $this->assertInstanceOf(Payment::class, $reservation->payment);
        $this->assertEquals($payment->id, $reservation->payment->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $reservation = new Reservation;
        $casts = $reservation->getCasts();

        $this->assertEquals('datetime:Y-m-d', $casts['check_in']);
        $this->assertEquals('datetime:Y-m-d', $casts['check_out']);
        $this->assertEquals(ReservationStatus::class, $casts['status']);
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
        ]);

        $array = $reservation->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_can_check_if_cancelled(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $cancelledByGuest = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_GUEST->value,
        ]);

        $cancelledByHost = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_HOST->value,
        ]);

        $confirmed = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED->value,
        ]);

        $this->assertTrue($cancelledByGuest->isCancelled());
        $this->assertTrue($cancelledByHost->isCancelled());
        $this->assertFalse($confirmed->isCancelled());
    }

    #[Test]
    public function it_can_filter_active_reservations(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $confirmed = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CONFIRMED->value,
        ]);

        $completed = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::COMPLETED->value,
        ]);

        $cancelled = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'status' => ReservationStatus::CANCELLED_BY_GUEST->value,
        ]);

        $active = Reservation::query()
            ->active()
            ->get();

        $this->assertCount(2, $active);
        $this->assertTrue($active->contains('id', $confirmed->id));
        $this->assertTrue($active->contains('id', $completed->id));
        $this->assertFalse($active->contains('id', $cancelled->id));
    }

    #[Test]
    public function it_can_filter_by_guest(): void
    {
        $guest1 = User::factory()->create();
        $guest2 = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest1->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest2->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
        ]);

        $filtered = Reservation::query()
            ->where('guest_id', $guest1->id)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($reservation1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_date_range(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->create();
        $listing = Listing::factory()->create([
            'host_id' => $host->id,
        ]);

        $now = now();

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'check_in' => $now->copy()->addDays(1),
            'check_out' => $now->copy()->addDays(5),
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => 'listing',
            'check_in' => $now->copy()->addDays(10),
            'check_out' => $now->copy()->addDays(15),
        ]);

        $filtered = Reservation::query()
            ->where('check_in', '<=', $now->copy()->addDays(3))
            ->where('check_out', '>=', $now->copy()->addDays(3))
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($reservation1->id, $filtered->first()->id);
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
            'total_price' => 10000,
        ]);

        $this->assertInstanceOf(Money::class, $reservation->total_price);
        $this->assertEquals(10000, $reservation->total_price->getAmount());
        $this->assertEquals('USD', $reservation->total_price->getCurrency()->getCode());
    }
}
