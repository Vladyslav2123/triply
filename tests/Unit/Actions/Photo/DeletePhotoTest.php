<?php

namespace Tests\Unit\Actions\Photo;

use App\Actions\Photo\DeletePhoto;
use App\Models\Photo;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DeletePhotoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_photo_from_database_and_storage(): void
    {
        // Setup
        Storage::fake('s3');
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // Create a test file in the fake storage
        $testFilePath = 'profiles/test-photo.jpg';
        Storage::disk('s3')->put($testFilePath, 'test content');

        // Create a photo record
        $photo = Photo::factory()->create([
            'url' => $testFilePath,
            'disk' => 's3',
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ]);

        // Verify the file exists
        $this->assertTrue(Storage::disk('s3')->exists($testFilePath));

        // Execute the action
        $action = new DeletePhoto;
        $action->execute($photo);

        // Assert the file is deleted from storage
        $this->assertFalse(Storage::disk('s3')->exists($testFilePath));

        // Assert the photo is deleted from database
        $this->assertNull(Photo::find($photo->id));
        $this->assertNotNull(Photo::withTrashed()->find($photo->id));
    }

    #[Test]
    public function it_does_not_delete_default_avatar_from_storage(): void
    {
        // Setup
        Storage::fake('s3');
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // Create the default avatar file in the fake storage
        $defaultAvatarPath = PhotoConstants::DEFAULT_AVATAR_PATH;
        Storage::disk('s3')->put($defaultAvatarPath, 'default avatar content');

        // Create a photo record with the default avatar path
        $photo = Photo::factory()->create([
            'url' => $defaultAvatarPath,
            'disk' => 's3',
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ]);

        // Execute the action
        $action = new DeletePhoto;
        $action->execute($photo);

        // Assert the default avatar file still exists in storage
        $this->assertTrue(Storage::disk('s3')->exists($defaultAvatarPath));

        // Assert the photo is deleted from database
        $this->assertNull(Photo::find($photo->id));
        $this->assertNotNull(Photo::withTrashed()->find($photo->id));
    }

    #[Test]
    public function it_handles_nonexistent_files_gracefully(): void
    {
        // Setup
        Storage::fake('s3');
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // Create a photo record with a path that doesn't exist in storage
        $photo = Photo::factory()->create([
            'url' => 'profiles/nonexistent-file.jpg',
            'disk' => 's3',
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ]);

        // Execute the action - should not throw an exception
        $action = new DeletePhoto;
        $action->execute($photo);

        // Assert the photo is deleted from database
        $this->assertNull(Photo::find($photo->id));
        $this->assertNotNull(Photo::withTrashed()->find($photo->id));
    }
}
