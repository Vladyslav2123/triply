<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'photo']);

        $this->assertNotNull($this->user->profile->fresh()->photo);
    }

    public function test_user_can_delete_profile_photo(): void
    {
        // First upload a photo
        Storage::fake('s3');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->user)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        // Then delete it
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/profile/photo');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertNull($this->user->profile->fresh()->photo);
    }

    public function test_photo_upload_validates_file_type(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    public function test_photo_upload_validates_file_size(): void
    {
        Storage::fake('s3');

        // Create a file that's too large (6MB)
        $file = UploadedFile::fake()->image('large-avatar.jpg')->size(6000);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photo']);
    }

    public function test_unauthenticated_user_cannot_upload_photo(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/v1/profile/photo', [
            'photo' => $file,
        ]);

        $response->assertStatus(401);
    }
}
