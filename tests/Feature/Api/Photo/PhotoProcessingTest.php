<?php

namespace Tests\Feature\Api\Photo;

use App\Actions\Photo\CreatePhoto;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhotoProcessingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Listing $listing;

    #[Test]
    public function photos_are_saved_with_consistent_dimensions(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('test-photo.jpg', 1200, 800);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => PhotoConstants::DIRECTORY_LISTINGS,
            ]);

        $response->assertStatus(201);

        $photoId = $response->json('id');
        $photo = Photo::find($photoId);

        $this->assertNotNull($photo);
        $this->assertNotNull($photo->width);
        $this->assertNotNull($photo->height);

        $this->assertEquals(1200, $photo->width);
        $this->assertEquals(800, $photo->height);
    }

    #[Test]
    public function large_photos_are_resized_to_max_dimensions(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('large-photo.jpg', 2000, 1500);

        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/photos', [
                'file' => $file,
                'photoable_type' => 'listing',
                'photoable_id' => $this->listing->id,
                'directory' => PhotoConstants::DIRECTORY_LISTINGS,
            ]);

        $response->assertStatus(201);

        $photoId = $response->json('id');
        $photo = Photo::find($photoId);

        $this->assertNotNull($photo);
        $this->assertNotNull($photo->width);
        $this->assertNotNull($photo->height);

        $this->assertGreaterThan(0, $photo->width);
        $this->assertGreaterThan(0, $photo->height);

        $originalRatio = 2000 / 1500;
        $newRatio = $photo->width / $photo->height;
        $this->assertEqualsWithDelta($originalRatio, $newRatio, 0.1);
    }

    #[Test]
    public function photo_dimensions_are_recorded_correctly(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('test-photo.jpg', 800, 600);

        $createPhoto = app(CreatePhoto::class);
        $photo = $createPhoto->execute($this->listing, $file);

        $this->assertEquals(800, $photo->width);
        $this->assertEquals(600, $photo->height);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'host',
        ]);

        $this->listing = Listing::factory()->create([
            'host_id' => $this->user->id,
        ]);
    }
}
