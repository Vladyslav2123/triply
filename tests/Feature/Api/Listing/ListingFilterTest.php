<?php

namespace Tests\Feature\Api\Listing;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $guest;

    protected function setUp(): void
    {
        parent::setUp();

        // Створюємо користувачів для тестів
        $this->user = User::factory()->create();
        $this->guest = User::factory()->create();

        // Створюємо профілі для користувачів, якщо вони не існують
        if (! $this->user->profile) {
            Profile::factory()->create(['user_id' => $this->user->id]);
        }

        if (! $this->guest->profile) {
            Profile::factory()->create(['user_id' => $this->guest->id]);
        }

        // Створюємо додаткових користувачів для favorites в ListingFactory
        User::factory()->count(5)->create();
    }

    /**
     * Тест фільтрації оголошень за датами бронювання
     */
    public function test_filter_by_dates(): void
    {
        $listing = Listing::factory()->create([
            'host_id' => $this->user->id,
        ]);

        // Використовуємо дати в майбутньому (наступний рік)
        $nextYear = now()->addYear()->year;
        $checkInDate = $nextYear.'-02-10';
        $checkOutDate = $nextYear.'-02-15';
        $searchCheckIn = $nextYear.'-02-16';
        $searchCheckOut = $nextYear.'-02-20';

        Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $listing->id,
            'reservationable_type' => Listing::class,
            'check_in' => $checkInDate,
            'check_out' => $checkOutDate,
            'status' => ReservationStatus::CONFIRMED,
        ]);

        $response = $this->getJson("/api/v1/listings?check_in={$searchCheckIn}&check_out={$searchCheckOut}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест фільтрації оголошень за кількістю гостей
     */
    public function test_filter_by_guests(): void
    {
        Listing::factory()->create([
            'host_id' => $this->user->id,
            'house_rules' => [
                'pets_allowed' => false,
                'events_allowed' => false,
                'smoking_allowed' => false,
                'quiet_hours' => false,
                'commercial_photography_allowed' => false,
                'number_of_guests' => 2,
                'additional_rules' => 'No parties',
                'parties_allowed' => false,
                'restricted_areas' => [],
            ],
        ]);

        Listing::factory()->create([
            'host_id' => $this->user->id,
            'house_rules' => [
                'pets_allowed' => false,
                'events_allowed' => false,
                'smoking_allowed' => false,
                'quiet_hours' => false,
                'commercial_photography_allowed' => false,
                'number_of_guests' => 4,
                'additional_rules' => 'No parties',
                'parties_allowed' => false,
                'restricted_areas' => [],
            ],
        ]);

        $response = $this->getJson('/api/v1/listings?guests=3');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест фільтрації оголошень за рейтингом
     */
    public function test_filter_by_rating(): void
    {
        // Створюємо перше оголошення з рейтингом 5 зірок
        $listing1 = Listing::factory()->create([
            'host_id' => $this->user->id,
            'title' => 'Test Listing 1',
            'slug' => 'test-listing-1',
        ]);

        $reservation1 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $listing1->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation1->id,
            'reviewer_id' => $this->guest->id,
            'overall_rating' => 5.0,
        ]);

        // Створюємо друге оголошення з рейтингом 3 зірки
        $listing2 = Listing::factory()->create([
            'host_id' => $this->user->id,
            'title' => 'Test Listing 2',
            'slug' => 'test-listing-2',
        ]);

        $reservation2 = Reservation::factory()->create([
            'guest_id' => $this->guest->id,
            'reservationable_id' => $listing2->id,
            'reservationable_type' => Listing::class,
            'status' => ReservationStatus::COMPLETED,
        ]);

        Review::factory()->create([
            'reservation_id' => $reservation2->id,
            'reviewer_id' => $this->guest->id,
            'overall_rating' => 3.0,
        ]);

        // Оновлюємо оголошення, щоб переконатися, що рейтинги розраховані
        $listing1->refresh();
        $listing2->refresh();

        $response = $this->getJson('/api/v1/listings?min_rating=4');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $listing1->id);
    }
}
