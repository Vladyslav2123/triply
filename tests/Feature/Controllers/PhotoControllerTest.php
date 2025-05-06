<?php

namespace Tests\Feature\Controllers;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class PhotoControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $host;

    private Listing $listing;

    private Photo $photo;

    #[Test]
    public function index_returns_paginated_photos(): void
    {
        Photo::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/photos');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'url',
                        'photoable_type',
                        'photoable_id',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function show_returns_photo_details(): void
    {
        $response = $this->actingAs($this->host)
            ->getJson("/api/v1/photos/{$this->photo->id}");

        dump($response->json());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'url',
                    'photoable_type',
                    'photoable_id',
                ],
            ])
            ->assertJsonPath('data.id', $this->photo->id);
    }

    #[Test]
    public function store_creates_new_photo(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('test-photo.jpg');

        $photoData = [
            'file' => $file,
            'photoable_type' => 'listing',
            'photoable_id' => $this->listing->id,
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', $photoData);

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
    public function store_validates_photo_data(): void
    {
        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', [
                //
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file', 'photoable_type', 'photoable_id']);
    }

    #[Test]
    public function store_validates_file_type(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $photoData = [
            'file' => $file,
            'photoable_type' => 'listing',
            'photoable_id' => $this->listing->id,
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', $photoData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function store_validates_file_size(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->create('large-image.jpg', 6000, 'image/jpeg');

        $photoData = [
            'file' => $file,
            'photoable_type' => 'listing',
            'photoable_id' => $this->listing->id,
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', $photoData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    #[Test]
    public function store_creates_photo_for_profile(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $profile = $user->getOrCreateProfile();

        $file = UploadedFile::fake()->image('profile-photo.jpg');

        $photoData = [
            'file' => $file,
            'photoable_type' => 'profile',
            'photoable_id' => $profile->id,
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/v1/photos', $photoData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'url',
                'photoable_type',
                'photoable_id',
            ])
            ->assertJsonPath('photoable_type', 'profile')
            ->assertJsonPath('photoable_id', $profile->id);
    }

    #[Test]
    public function store_creates_photo_for_experience(): void
    {
        Storage::fake('s3');

        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $file = UploadedFile::fake()->image('experience-photo.jpg');

        $photoData = [
            'file' => $file,
            'photoable_type' => 'experience',
            'photoable_id' => $experience->id,
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/photos', $photoData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'url',
                'photoable_type',
                'photoable_id',
            ])
            ->assertJsonPath('photoable_type', 'experience')
            ->assertJsonPath('photoable_id', $experience->id);
    }

    #[Test]
    public function destroy_deletes_photo(): void
    {
        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/photos/{$this->photo->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('photos', ['id' => $this->photo->id]);
    }

    #[Test]
    public function destroy_requires_authorization(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/photos/{$this->photo->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('photos', ['id' => $this->photo->id]);
    }

    #[Test]
    public function admin_can_access_any_photo(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/photos/{$this->photo->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->photo->id);
    }

    #[Test]
    public function admin_can_delete_any_photo(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/photos/{$this->photo->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('photos', ['id' => $this->photo->id]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create([
            'role' => 'host',
        ]);

        $this->listing = Listing::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $this->photo = Photo::factory()->create([
            'photoable_id' => $this->listing->id,
            'photoable_type' => 'listing',
        ]);
    }
}
