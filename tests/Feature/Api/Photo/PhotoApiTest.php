<?php

namespace Tests\Feature\Api\Photo;

use App\Models\Listing;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhotoApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $host;

    private Listing $listing;

    #[Test]
    public function host_can_upload_photo_for_listing(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('listing-photo.jpg');

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => PhotoConstants::DIRECTORY_LISTINGS,
                'caption' => 'Living room',
                'is_primary' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'url',
                'photoable_type',
                'photoable_id',
            ]);

        $this->assertDatabaseHas('photos', [
            'photoable_id' => $this->listing->id,
            'photoable_type' => 'listing',
        ]);
    }

    #[Test]
    public function non_host_cannot_upload_photo_for_listing(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('listing-photo.jpg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => 'listings/photos',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function host_can_delete_photo(): void
    {
        $photo = Photo::factory()->create([
            'photoable_id' => $this->listing->id,
            'photoable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/photos/{$photo->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('photos', [
            'id' => $photo->id,
        ]);
    }

    #[Test]
    public function non_host_cannot_delete_photo(): void
    {
        $photo = Photo::factory()->create([
            'photoable_id' => $this->listing->id,
            'photoable_type' => 'listing',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/photos/{$photo->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function photo_upload_validates_file_type(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => 'listings/photos',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function photo_upload_validates_file_size(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('large-photo.jpg')->size(6000);

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => 'listings/photos',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->host = User::factory()->create();
        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);
    }
}
