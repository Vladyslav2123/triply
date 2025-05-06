<?php

namespace Tests\Feature\Api\ExperienceAvailability;

use App\Models\Experience;
use App\Models\ExperienceAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceAvailabilityApiTest extends TestCase
{
    use RefreshDatabase;

    private User $host;

    private Experience $experience;

    protected function setUp(): void
    {
        parent::setUp();
        $this->host = User::factory()->create();
        $this->experience = Experience::factory()->create([
            'host_id' => $this->host->id,
        ]);
    }

    public function test_host_can_set_availability_for_experience(): void
    {
        $availabilityData = [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/experiences/{$this->experience->id}/availability", $availabilityData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'date',
                'is_available',
                'slots',
            ]);

        $this->assertDatabaseHas('experience_availabilities', [
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ]);
    }

    public function test_non_host_cannot_set_availability_for_experience(): void
    {
        $user = User::factory()->create();

        $availabilityData = [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ];

        $response = $this->actingAs($user) // Not the host
            ->postJson("/api/v1/experiences/{$this->experience->id}/availability", $availabilityData);

        $response->assertStatus(403);
    }

    public function test_host_can_update_availability(): void
    {
        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ]);

        $updateData = [
            'is_available' => false,
            'slots' => 5,
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/experience-availabilities/{$availability->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('is_available', false)
            ->assertJsonPath('slots', 5);

        $this->assertDatabaseHas('experience_availabilities', [
            'id' => $availability->id,
            'is_available' => false,
            'slots' => 5,
        ]);
    }

    public function test_non_host_cannot_update_availability(): void
    {
        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
        ]);

        $user = User::factory()->create();

        $updateData = [
            'is_available' => false,
        ];

        $response = $this->actingAs($user) // Not the host
            ->putJson("/api/v1/experience-availabilities/{$availability->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_host_can_delete_availability(): void
    {
        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
        ]);

        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/experience-availabilities/{$availability->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('experience_availabilities', [
            'id' => $availability->id,
        ]);
    }

    public function test_non_host_cannot_delete_availability(): void
    {
        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user) // Not the host
            ->deleteJson("/api/v1/experience-availabilities/{$availability->id}");

        $response->assertStatus(403);
    }

    public function test_can_get_availability_for_experience(): void
    {
        // Create availabilities for different dates
        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ]);

        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'is_available' => false,
            'slots' => 0,
        ]);

        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}/availability");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'is_available',
                        'slots',
                    ],
                ],
            ]);
    }

    public function test_can_get_availability_for_date_range(): void
    {
        // Create availabilities for different dates
        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
        ]);

        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(10)->format('Y-m-d'),
            'is_available' => true,
        ]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->addDays(5)->format('Y-m-d');

        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}/availability?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_host_can_set_bulk_availability(): void
    {
        $bulkData = [
            'dates' => [
                now()->addDays(1)->format('Y-m-d'),
                now()->addDays(2)->format('Y-m-d'),
                now()->addDays(3)->format('Y-m-d'),
            ],
            'is_available' => true,
            'slots' => 15,
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/experiences/{$this->experience->id}/bulk-availability", $bulkData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'count',
            ])
            ->assertJsonPath('count', 3);

        // Check that all dates were created
        foreach ($bulkData['dates'] as $date) {
            $this->assertDatabaseHas('experience_availabilities', [
                'experience_id' => $this->experience->id,
                'date' => $date,
                'is_available' => true,
                'slots' => 15,
            ]);
        }
    }

    public function test_host_can_set_recurring_availability(): void
    {
        $recurringData = [
            'day_of_week' => 1, // Monday
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(1)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ];

        $response = $this->actingAs($this->host)
            ->postJson("/api/v1/experiences/{$this->experience->id}/recurring-availability", $recurringData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'count',
            ]);

        // Count the number of Mondays in the date range
        $startDate = now();
        $endDate = now()->addMonths(1);
        $mondays = 0;

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            if ($date->dayOfWeek === 1) { // Monday
                $mondays++;
                $this->assertDatabaseHas('experience_availabilities', [
                    'experience_id' => $this->experience->id,
                    'date' => $date->format('Y-m-d'),
                    'is_available' => true,
                    'slots' => 10,
                ]);
            }
        }

        // Check that the correct number of dates were created
        $this->assertEquals($mondays, $response->json('count'));
    }

    public function test_can_check_if_experience_has_available_slots(): void
    {
        // Create an availability with slots
        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 10,
        ]);

        // Create an availability with no slots
        ExperienceAvailability::factory()->create([
            'experience_id' => $this->experience->id,
            'date' => now()->addDays(2)->format('Y-m-d'),
            'is_available' => true,
            'slots' => 0,
        ]);

        // Check date with available slots
        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}/check-availability?date=".now()->addDays(1)->format('Y-m-d').'&guests=5');

        $response->assertStatus(200)
            ->assertJson([
                'is_available' => true,
                'available_slots' => 10,
            ]);

        // Check date with no available slots
        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}/check-availability?date=".now()->addDays(2)->format('Y-m-d').'&guests=5');

        $response->assertStatus(200)
            ->assertJson([
                'is_available' => false,
                'available_slots' => 0,
            ]);

        // Check date with not enough slots
        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}/check-availability?date=".now()->addDays(1)->format('Y-m-d').'&guests=15');

        $response->assertStatus(200)
            ->assertJson([
                'is_available' => false,
                'available_slots' => 10,
            ]);
    }
}
