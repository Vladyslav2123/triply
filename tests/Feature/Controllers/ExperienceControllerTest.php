<?php

namespace Tests\Feature\Controllers;

use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Models\Experience;
use App\Models\User;
use App\ValueObjects\Experience\Pricing;
use App\ValueObjects\Location;
use Illuminate\Foundation\Testing\WithFaker;

class ExperienceControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $host;

    private Experience $experience;

    /**
     * Test retrieving a list of experiences
     */
    public function test_index_returns_paginated_experiences(): void
    {
        // Create additional experiences
        Experience::factory()->count(5)->create([
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/experiences');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'host_id',
                        'category',
                        'location',
                        'status',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Test retrieving a specific experience
     */
    public function test_show_returns_experience_details(): void
    {
        $response = $this->getJson("/api/v1/experiences/{$this->experience->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'host_id',
                'category',
                'location',
                'status',
            ])
            ->assertJsonPath('id', $this->experience->id);
    }

    /**
     * Test creating a new experience
     */
    public function test_store_creates_new_experience(): void
    {
        $experienceData = [
            'title' => 'Test Experience',
            'description' => 'This is a test experience description',
            'category' => ExperienceType::WORKSHOP->value,
            'sub_category' => 'Cooking',
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Kyiv',
                'address' => 'Test Address',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
            ],
            'languages' => ['en', 'uk'],
            'pricing' => [
                'base_price' => 5000, // 50.00 in cents
                'currency' => 'UAH',
                'per_person' => true,
            ],
            'duration' => '2023-12-31',
            'guest_requirements' => [
                'minimum_age' => 18,
                'can_bring_children_under_2' => true,
                'accessibility_communication' => false,
                'accessibility_mobility' => false,
                'accessibility_sensory' => false,
                'physical_activity_level' => \App\Enums\PhysicalActivityLevel::LOW->value,
                'skill_level' => \App\Enums\SkillLevel::BEGINNER->value,
                'additional_requirements' => null,
            ],
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/experiences', $experienceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'host_id',
                'category',
                'location',
                'status',
            ]);

        $this->assertDatabaseHas('experiences', [
            'title' => 'Test Experience',
            'host_id' => $this->host->id,
        ]);
    }

    /**
     * Test validation when creating an experience
     */
    public function test_store_validates_experience_data(): void
    {
        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/experiences', [
                // Missing required fields
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category']);
    }

    /**
     * Test updating an experience
     */
    public function test_update_modifies_experience(): void
    {
        $updateData = [
            'title' => 'Updated Experience Title',
            'description' => 'Updated experience description',
            'pricing' => [
                'base_price' => 7500, // 75.00 in cents
                'currency' => 'UAH',
                'per_person' => true,
            ],
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/experiences/{$this->experience->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Experience Title')
            ->assertJsonPath('description', 'Updated experience description');

        $this->assertDatabaseHas('experiences', [
            'id' => $this->experience->id,
            'title' => 'Updated Experience Title',
            'description' => 'Updated experience description',
        ]);
    }

    /**
     * Test authorization when updating an experience
     */
    public function test_update_requires_authorization(): void
    {
        $otherUser = User::factory()->create();

        $updateData = [
            'title' => 'Unauthorized Update Attempt',
        ];

        $response = $this->actingAs($otherUser)
            ->putJson("/api/v1/experiences/{$this->experience->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test deleting an experience
     */
    public function test_destroy_deletes_experience(): void
    {
        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/experiences/{$this->experience->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('experiences', ['id' => $this->experience->id]);
    }

    /**
     * Test authorization when deleting an experience
     */
    public function test_destroy_requires_authorization(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->deleteJson("/api/v1/experiences/{$this->experience->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('experiences', ['id' => $this->experience->id]);
    }

    /**
     * Test filtering experiences by location
     */
    public function test_index_filters_experiences_by_location(): void
    {
        // Create experiences in different locations
        $kyivLocation = new Location(
            country: 'Ukraine',
            city: 'Kyiv',
            address: 'Test Address',
            latitude: 50.4501,
            longitude: 30.5234
        );

        $lvivLocation = new Location(
            country: 'Ukraine',
            city: 'Lviv',
            address: 'Test Address',
            latitude: 49.8397,
            longitude: 24.0297
        );

        Experience::factory()->create([
            'location' => $kyivLocation,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        Experience::factory()->create([
            'location' => $lvivLocation,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/experiences?city=Kyiv');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.location.city', 'Kyiv');
    }

    /**
     * Test filtering experiences by category
     */
    public function test_index_filters_experiences_by_category(): void
    {
        // Create experiences with different categories
        Experience::factory()->create([
            'category' => ExperienceType::NATURE,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        Experience::factory()->create([
            'category' => ExperienceType::WORKSHOP,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/experiences?category=workshop');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.category', ExperienceType::WORKSHOP->value);
    }

    /**
     * Test filtering experiences by price range
     */
    public function test_index_filters_experiences_by_price_range(): void
    {
        // Create experiences with different prices
        $lowPriceExperience = Experience::factory()->create([
            'pricing' => new Pricing(
                base_price: 2000, // 20.00
                currency: 'UAH',
                per_person: true
            ),
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $highPriceExperience = Experience::factory()->create([
            'pricing' => new Pricing(
                base_price: 10000, // 100.00
                currency: 'UAH',
                per_person: true
            ),
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/experiences?min_price=5000&max_price=15000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test getting experiences by host
     */
    public function test_get_host_experiences(): void
    {
        // Create additional experiences for the host
        Experience::factory()->count(3)->create([
            'host_id' => $this->host->id,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        // Create experiences for another host
        $otherHost = User::factory()->create(['role' => 'host']);
        Experience::factory()->count(2)->create([
            'host_id' => $otherHost->id,
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson("/api/v1/hosts/{$this->host->id}/experiences");

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data'); // 3 new + 1 created in setUp
    }

    /**
     * Test searching experiences
     */
    public function test_index_searches_experiences(): void
    {
        // Create experiences with specific titles
        Experience::factory()->create([
            'title' => 'Cooking Workshop in Kyiv',
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        Experience::factory()->create([
            'title' => 'City Tour in Lviv',
            'status' => ExperienceStatus::PUBLISHED,
        ]);

        $response = $this->getJson('/api/v1/experiences?search=Cooking');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.title', 'Cooking Workshop in Kyiv');
    }

    /**
     * Test admin can manage any experience
     */
    public function test_admin_can_manage_any_experience(): void
    {
        $updateData = [
            'title' => 'Admin Updated Title',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/experiences/{$this->experience->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Admin Updated Title');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a host user and an experience
        $this->host = User::factory()->create([
            'role' => 'host',
        ]);

        $this->experience = Experience::factory()->create([
            'host_id' => $this->host->id,
            'status' => ExperienceStatus::PUBLISHED,
        ]);
    }
}
