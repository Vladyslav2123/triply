<?php

namespace Tests\Unit\Models;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_disk_configuration(): void
    {
        $photo = Photo::factory()->create(['disk' => 's3']);

        $this->assertEquals('s3', $photo->disk);
        $this->assertEquals('photos', $photo->directory);
    }

    #[Test]
    public function it_stores_image_metadata(): void
    {
        $photo = Photo::factory()->create([
            'mime_type' => 'image/jpeg',
            'width' => 1920,
            'height' => 1080,
            'size' => 1024000,
        ]);

        $this->assertEquals('image/jpeg', $photo->mime_type);
        $this->assertEquals(1920, $photo->width);
        $this->assertEquals(1080, $photo->height);
        $this->assertEquals(1024000, $photo->size);
    }

    #[Test]
    public function it_tracks_upload_timestamp(): void
    {
        $now = now();
        $photo = Photo::factory()->create(['uploaded_at' => $now]);

        $this->assertEquals($now->format('Y-m-d'), $photo->uploaded_at->format('Y-m-d'));
    }

    #[Test]
    public function it_generates_correct_full_url_for_different_disks(): void
    {
        Storage::fake('s3');
        Storage::fake('public');

        $s3Photo = Photo::factory()->create([
            'disk' => 's3',
            'url' => 'photos/test-s3.jpg',
        ]);

        $publicPhoto = Photo::factory()->create([
            'disk' => 'public',
            'url' => 'photos/test-public.jpg',
        ]);

        $this->assertEquals(
            Storage::disk('s3')->url('photos/test-s3.jpg'),
            $s3Photo->full_url
        );

        $this->assertEquals(
            Storage::disk('public')->url('photos/test-public.jpg'),
            $publicPhoto->full_url
        );
    }

    #[Test]
    public function it_stores_original_filename(): void
    {
        $photo = Photo::factory()->create([
            'original_filename' => 'vacation-photo.jpg',
        ]);

        $this->assertEquals('vacation-photo.jpg', $photo->original_filename);
    }

    #[Test]
    public function it_can_handle_multiple_photos_for_same_photoable(): void
    {
        $host = User::factory()->create();
        $listing = Listing::factory()->state(['host_id' => $host->id])->create();

        $listing->refresh();

        $this->assertCount(3, $listing->photos);

        foreach ($listing->photos as $photo) {
            $this->assertInstanceOf(Listing::class, $photo->photoable);
            $this->assertEquals($listing->id, $photo->photoable->id);
            $this->assertEquals($host->id, $photo->photoable->host_id);
        }
    }

    #[Test]
    public function it_returns_correct_array_representation(): void
    {
        Storage::fake('s3');

        $photo = Photo::factory()->create([
            'url' => 'photos/test.jpg',
            'disk' => 's3',
            'directory' => 'photos',
            'size' => 1024000,
            'original_filename' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'width' => 1920,
            'height' => 1080,
        ]);

        $array = $photo->toArray();

        $expectedKeys = [
            'id',
            'url',
            'disk',
            'directory',
            'size',
            'original_filename',
            'mime_type',
            'width',
            'height',
            'photoable_type',
            'photoable_id',
            'full_url',
            'uploaded_at',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $array);
        }

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_validates_photoable_type(): void
    {
        $validTypes = ['profile', 'listing', 'experience'];

        foreach ($validTypes as $type) {
            $user = User::factory()->create();
            $model = match ($type) {
                'profile' => $user->getOrCreateProfile(),
                'listing' => Listing::factory()->state(['host_id' => $user->id])->create(),
                'experience' => Experience::factory()->state(['host_id' => $user->id])->create(),
            };

            $photo = Photo::factory()->create([
                'photoable_type' => $type,
                'photoable_id' => $model->id,
            ]);

            $this->assertEquals($type, $photo->photoable_type);
            $this->assertEquals($model->id, $photo->photoable_id);
        }
    }

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $photo = Photo::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $photo->id);
    }

    #[Test]
    public function it_has_photoable_relationship(): void
    {
        $photo = new Photo;

        $this->assertInstanceOf(MorphTo::class, $photo->photoable());
    }

    #[Test]
    public function it_can_belong_to_user(): void
    {
        $user = User::factory()->create();
        $photo = Photo::factory()->create([
            'photoable_type' => 'user',
            'photoable_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $photo->photoable);
        $this->assertEquals($user->id, $photo->photoable->id);
    }

    #[Test]
    public function it_can_belong_to_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->getOrCreateProfile();
        $photo = Photo::factory()->create([
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ]);

        $this->assertInstanceOf(Profile::class, $photo->photoable);
        $this->assertEquals($profile->id, $photo->photoable->id);
    }

    #[Test]
    public function it_can_belong_to_listing(): void
    {
        $user = User::factory()->create();
        $listing = Listing::factory()->create(['host_id' => $user->id]);
        $photo = Photo::factory()->create([
            'photoable_type' => 'listing',
            'photoable_id' => $listing->id,
        ]);

        $this->assertInstanceOf(Listing::class, $photo->photoable);
        $this->assertEquals($listing->id, $photo->photoable->id);
        $this->assertEquals($user->id, $photo->photoable->host_id);
    }

    #[Test]
    public function it_can_belong_to_experience(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create(['host_id' => $user->id]);
        $photo = Photo::factory()->create([
            'photoable_type' => 'experience',
            'photoable_id' => $experience->id,
        ]);

        $this->assertInstanceOf(Experience::class, $photo->photoable);
        $this->assertEquals($experience->id, $photo->photoable->id);
        $this->assertEquals($user->id, $photo->photoable->host_id);
    }

    #[Test]
    public function it_generates_full_url(): void
    {
        Storage::fake('s3');

        $photo = Photo::factory()->create([
            'url' => 'photos/test.jpg',
        ]);

        $expectedUrl = Storage::disk('s3')->url('photos/test.jpg');

        $this->assertEquals($expectedUrl, $photo->full_url);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $photo = Photo::factory()->create();
        $array = $photo->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_includes_full_url_in_array(): void
    {
        Storage::fake('s3');

        $photo = Photo::factory()->create([
            'url' => 'photos/test.jpg',
        ]);

        $array = $photo->toArray();

        $this->assertArrayHasKey('full_url', $array);
        $this->assertEquals(Storage::disk('s3')->url('photos/test.jpg'), $array['full_url']);
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $photo = Photo::factory()->create();
        $photoId = $photo->id;

        $photo->delete();

        $this->assertNull(Photo::find($photoId));
        $this->assertNotNull(Photo::withTrashed()->find($photoId));
        $this->assertInstanceOf(Carbon::class, $photo->deleted_at);
    }
}
