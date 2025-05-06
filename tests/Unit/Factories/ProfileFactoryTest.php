<?php

namespace Tests\Unit\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileFactoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_profile_with_factory()
    {
        $user = User::factory()->create();

        $profile = Profile::factory()->forUser($user)->create();

        $this->assertNotNull($profile);
        $this->assertEquals($user->id, $profile->user_id);

        $this->assertEquals(2, Profile::where('user_id', $user->id)->count());
    }

    #[Test]
    public function it_creates_profile_with_correct_fields()
    {
        $user = User::factory()->create();

        if ($user->profile) {
            $user->profile->delete();
            $user->refresh();
        }

        $profile = Profile::factory()->forUser($user)->create();

        $this->assertNotNull($profile->first_name);
        $this->assertNotNull($profile->last_name);
        $this->assertNotNull($profile->birth_date);
        $this->assertNotNull($profile->gender);
        $this->assertNotNull($profile->preferred_language);
        $this->assertNotNull($profile->preferred_currency);
    }
}
