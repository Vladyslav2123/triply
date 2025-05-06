<?php

namespace Tests\Unit\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCleaningSequenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * First test in sequence - verifies database is empty and creates records.
     */
    public function test_first_in_sequence(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test');

        User::factory()->count(3)->create();

        $this->assertEquals(3, User::count(), 'Database should have 3 users after creation');
    }

    /**
     * Second test in sequence - verifies database is cleaned between tests.
     */
    public function test_second_in_sequence(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test, even after previous test');

        User::factory()->count(5)->create();

        $this->assertEquals(5, User::count(), 'Database should have 5 users after creation');
    }

    /**
     * Third test in sequence - verifies database is cleaned again.
     */
    public function test_third_in_sequence(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test, even after previous tests');
    }

    /**
     * Test that database can be seeded.
     */
    public function test_database_seeding(): void
    {
        $this->assertEquals(0, User::count(), 'Database should be empty at the start of the test');

        $this->seed();

        $this->assertGreaterThan(0, User::count(), 'Database should have users after seeding');
    }
}
