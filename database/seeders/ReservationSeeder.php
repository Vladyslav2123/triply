<?php

namespace Database\Seeders;

use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create completed reservations for listings
        Listing::all()->each(function (Listing $listing) {
            // Create completed reservations first
            Reservation::factory(2)
                ->forReservable($listing)
                ->state(['status' => ReservationStatus::COMPLETED])
                ->create()
                ->each(function (Reservation $reservation) {
                    Review::factory()->forReservation($reservation)->create();
                });

            // Create some additional reservations with random statuses
            Reservation::factory(1)
                ->forReservable($listing)
                ->create();
        });

        // Create completed reservations for experiences
        Experience::all()->each(function (Experience $experience) {
            // Create completed reservations first
            Reservation::factory(2)
                ->forReservable($experience)
                ->state(['status' => ReservationStatus::COMPLETED])
                ->create()
                ->each(function (Reservation $reservation) {
                    Review::factory()->forReservation($reservation)->create();
                });

            // Create some additional reservations with random statuses
            Reservation::factory(1)
                ->forReservable($experience)
                ->create();
        });
    }
}
