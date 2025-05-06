<?php

namespace Tests\Unit\Database;

use App\Enums\ReservationStatus;
use App\Models\Listing;
use App\Models\Reservation;
use App\Models\User;
use Database\Seeders\ExperienceSeeder;
use Database\Seeders\ListingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeedingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that demonstrates how to seed the database for a specific test.
     */
    public function test_database_seeding_for_specific_test(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test');

        $this->seed(UserSeeder::class);

        $this->assertGreaterThan(0, User::count(), 'User table should have records after seeding');
        $this->assertEquals(0, Listing::count(), 'Listing table should still be empty');
    }

    /**
     * Test that demonstrates how to seed the entire database.
     */
    public function test_full_database_seeding(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test');
        $this->assertEquals(0, Listing::count(), 'Listing table should be empty at the start of the test');
        $this->assertEquals(0, Reservation::count(), 'Reservation table should be empty at the start of the test');

        $this->seed([
            UserSeeder::class,
            ListingSeeder::class,
            ExperienceSeeder::class,
        ]);

        $listing = Listing::first();
        Reservation::factory()
            ->count(5)
            ->forReservable($listing)
            ->state(['status' => ReservationStatus::COMPLETED])
            ->create();

        $this->assertDatabaseCount('reservations', 5);
        $this->assertEquals(5, Reservation::where('status', ReservationStatus::COMPLETED)->count());
    }

    /**
     * Test that demonstrates how to create custom test data.
     */
    public function test_custom_test_data(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test');

        User::factory()->create();

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $listing = Listing::factory()->create([
            'host_id' => $user->id,
            'title' => 'Test Listing',
        ]);

        $this->assertEquals(2, User::count(), 'User table should have 2 records');
        $this->assertEquals(1, Listing::count(), 'Listing table should have 1 record');
        $this->assertEquals('Test Listing', Listing::first()->title, 'Listing title should match');
    }
}
