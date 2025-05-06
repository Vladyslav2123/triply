<?php

namespace Tests\Feature\Api\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[Test]
    public function authenticated_user_can_get_own_data(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'slug',
                'email',
                'phone',
                'role',
                'is_banned',
                'profile',
            ])
            ->assertJsonPath('id', $this->user->id);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_user_data(): void
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }

    #[Test]
    public function user_cannot_update_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)
            ->putJson('/user/profile-information', [
                'email' => 'not-an-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_can_update_own_data(): void
    {
        $userData = [
            'name' => 'Jon Doe',
            'email' => 'updated@example.com',
            'phone' => '+380991234567',
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/user/profile-information', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'email' => 'updated@example.com',
            'phone' => '+380991234567',
        ]);

        $this->user->refresh();
    }

    #[Test]
    public function user_cannot_update_with_duplicate_email(): void
    {
        $anotherUser = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/user/profile-information', [
                'email' => 'existing@example.com',
                'phone' => '+380991234567',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_can_change_password(): void
    {
        $this->user->update([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/user/password', [
                'current_password' => 'current-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(200);

        $this->user->refresh();
        $this->assertTrue(password_verify('new-password', $this->user->password));
    }

    #[Test]
    public function user_cannot_change_password_with_incorrect_current_password(): void
    {
        $this->user->update([
            'password' => bcrypt('current-password'),
        ]);

        $response = $this->actingAs($this->user)
            ->putJson('/user/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
}
