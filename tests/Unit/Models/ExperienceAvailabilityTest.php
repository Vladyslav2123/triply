<?php

namespace Tests\Unit\Models;

use App\Models\Experience;
use App\Models\ExperienceAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExperienceAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $availability->id);
    }

    #[Test]
    public function it_has_experience_relationship(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
        ]);

        $this->assertInstanceOf(Experience::class, $availability->experience);
        $this->assertEquals($experience->id, $availability->experience->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $availability = new ExperienceAvailability;
        $casts = $availability->getCasts();

        $this->assertEquals('datetime:Y-m-d', $casts['date']);
        $this->assertEquals('boolean', $casts['is_available']);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
        ]);

        $array = $availability->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_can_be_marked_as_available(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
            'is_available' => true,
        ]);

        $this->assertTrue($availability->is_available);
    }

    #[Test]
    public function it_can_be_marked_as_unavailable(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
            'is_available' => false,
        ]);

        $this->assertFalse($availability->is_available);
    }

    #[Test]
    public function it_can_filter_by_date(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $date = now()->format('Y-m-d');

        $availability = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
            'date' => $date,
        ]);

        $filtered = ExperienceAvailability::query()
            ->where('date', $date)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($availability->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_experience(): void
    {
        $user = User::factory()->create();
        $experience1 = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $experience2 = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability1 = ExperienceAvailability::factory()->create([
            'experience_id' => $experience1->id,
        ]);

        $availability2 = ExperienceAvailability::factory()->create([
            'experience_id' => $experience2->id,
        ]);

        $filtered = ExperienceAvailability::query()
            ->where('experience_id', $experience1->id)
            ->get();

        $this->assertCount(1, $filtered);
        $this->assertEquals($availability1->id, $filtered->first()->id);
    }

    #[Test]
    public function it_can_filter_by_availability(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availabilityTrue = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
            'is_available' => true,
        ]);

        $availabilityFalse = ExperienceAvailability::factory()->create([
            'experience_id' => $experience->id,
            'is_available' => false,
        ]);

        $filteredAvailable = ExperienceAvailability::query()
            ->where('is_available', true)
            ->get();

        $this->assertCount(1, $filteredAvailable);
        $this->assertEquals($availabilityTrue->id, $filteredAvailable->first()->id);

        $filteredUnavailable = ExperienceAvailability::query()
            ->where('is_available', false)
            ->get();

        $this->assertCount(1, $filteredUnavailable);
        $this->assertEquals($availabilityFalse->id, $filteredUnavailable->first()->id);
    }
}
