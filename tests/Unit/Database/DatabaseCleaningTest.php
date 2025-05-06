<?php

namespace Tests\Unit\Database;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCleaningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the database is empty at the start of each test.
     */
    public function test_database_is_empty_at_start(): void
    {
        $this->assertEquals(0, User::count(), 'User table should be empty at the start of the test');
        $this->assertEquals(0, Listing::count(), 'Listing table should be empty at the start of the test');
        $this->assertEquals(0, Reservation::count(), 'Reservation table should be empty at the start of the test');
        $this->assertEquals(0, Review::count(), 'Review table should be empty at the start of the test');
    }

    /**
     * Test that the database is seeded correctly when using the RefreshDatabase trait.
     */
    public function test_database_can_be_seeded(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, User::count(), 'User table should have records after seeding');
        $this->assertGreaterThan(0, Listing::count(), 'Listing table should have records after seeding');
        $this->assertGreaterThan(0, Reservation::count(), 'Reservation table should have records after seeding');
        $this->assertGreaterThan(0, Review::count(), 'Review table should have records after seeding');
    }

    /**
     * Test that the database is cleaned between tests.
     */
    public function test_database_is_cleaned_between_tests(): void
    {
        User::factory()->count(5)->create();
        $this->assertEquals(5, User::count(), 'User table should have 5 records');
    }

    /**
     * Test that the database is still empty even after previous test created records.
     */
    public function test_database_is_still_empty_after_previous_test(): void
    {
        $this->assertEquals(0, User::count(), 'User table should be empty at the start of the test');
    }
}
