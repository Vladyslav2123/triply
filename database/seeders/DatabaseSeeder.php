<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProfileSeeder::class,
            ListingSeeder::class,
            ExperienceSeeder::class,
            ReservationSeeder::class,
            PaymentSeeder::class,
            FavoriteSeeder::class,
        ]);
    }
}
