<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileViewTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // Set up profile data for the other user
        $this->otherUser->profile->update([
            'first_name' => 'Other',
            'last_name' => 'User',
            'about' => 'This is another user profile',
            'work' => 'Developer',
            'company' => 'Tech Company',
        ]);
    }

    public function test_user_can_view_another_users_profile_by_id(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/profiles/{$this->otherUser->profile->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',
                'about',
                'work',
                'company',
            ])
            ->assertJsonPath('first_name', 'Other')
            ->assertJsonPath('last_name', 'User');
    }

    public function test_viewing_another_profile_increments_view_count(): void
    {
        $initialViewCount = $this->otherUser->profile->views_count ?? 0;

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/profiles/{$this->otherUser->profile->id}");

        $response->assertStatus(200);

        $this->otherUser->profile->refresh();
        $this->assertEquals($initialViewCount + 1, $this->otherUser->profile->views_count);
    }

    public function test_unauthenticated_user_can_view_public_profile(): void
    {
        $response = $this->getJson("/api/v1/profiles/{$this->otherUser->profile->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',
                'about',
            ]);
    }

    public function test_viewing_nonexistent_profile_returns_404(): void
    {
        $response = $this->getJson('/api/v1/profiles/nonexistent-id');

        $response->assertStatus(404);
    }

    public function test_user_can_view_profiles_by_user_id(): void
    {
        $response = $this->getJson("/api/v1/users/{$this->otherUser->id}/profile");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',
                'about',
            ])
            ->assertJsonPath('first_name', 'Other')
            ->assertJsonPath('last_name', 'User');
    }

    public function test_viewing_profile_by_nonexistent_user_id_returns_404(): void
    {
        $response = $this->getJson('/api/v1/users/nonexistent-id/profile');

        $response->assertStatus(404);
    }
}
