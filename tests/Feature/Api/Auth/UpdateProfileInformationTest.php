<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_update_profile_information(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->putJson('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $profile = $user->getOrCreateProfile();

        $response->assertStatus(200);

        $this->assertEquals('Updated Name', $profile->fresh()->first_name);
        $this->assertEquals('updated@example.com', $user->fresh()->email);
    }

    #[Test]
    public function user_cannot_update_profile_with_existing_email(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->actingAs($user1);

        $response = $this->putJson('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_update_profile_with_invalid_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->putJson('/user/profile-information', [
            'name' => 'Updated Name',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_update_profile_without_name(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->putJson('/user/profile-information', [
            'email' => 'valid@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
