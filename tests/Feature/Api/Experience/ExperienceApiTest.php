<?php

namespace Tests\Feature\Api\Experience;

use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Models\Experience;
use App\Models\User;
use App\ValueObjects\Experience\Pricing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExperienceApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $host;

    #[Test]
    public function can_get_experiences_list(): void
    {
        Experience::factory()->count(5)->create([
            'host_id' => $this->host->id,
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
                        'host',
                        'location',
                        'category',
                        'status',
                        'pricing',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function can_get_single_experience(): void
    {
        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
            'status' => ExperienceStatus::ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/experiences/{$experience->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'host',
                'location',
                'category',
                'status',
                'pricing',
                'languages',
                'duration',
                'grouping',
                'photos',
            ])
            ->assertJsonPath('id', $experience->id);
    }

    #[Test]
    public function can_create_experience_when_authenticated_as_host(): void
    {
        $experienceData = [
            'title' => 'Test Experience',
            'description' => 'This is a test experience description',
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Kyiv',
                'address' => 'Test Address',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
            ],
            'category' => ExperienceType::FOOD_AND_DRINK->value,
            'languages' => [Language::ENGLISH->value, Language::UKRAINIAN->value],
            'duration' => '3:00',
            'grouping' => [
                'generalGroupMax' => 10,
                'privateGroupMax' => 5,
            ],
            'pricing' => [
                'currency' => 'USD',
                'price_per_person' => 5000, // 50.00 in cents
                'private_group_min_price' => 20000, // 200.00 in cents
            ],
        ];

        $response = $this->actingAs($this->host)
            ->postJson('/api/v1/experiences', $experienceData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'description',
                'host',
                'location',
                'category',
                'status',
            ]);

        $this->assertDatabaseHas('experiences', [
            'title' => 'Test Experience',
            'host_id' => $this->host->id,
        ]);
    }

    #[Test]
    public function cannot_create_experience_when_unauthenticated(): void
    {
        $experienceData = [
            'title' => 'Test Experience',
            'description' => 'This is a test experience description',
        ];

        $response = $this->postJson('/api/v1/experiences', $experienceData);

        $response->assertStatus(401);
    }

    #[Test]
    public function can_update_own_experience(): void
    {
        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
            'title' => 'Original Title',
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->host)
            ->putJson("/api/v1/experiences/{$experience->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('title', 'Updated Title')
            ->assertJsonPath('description', 'Updated description');

        $this->assertDatabaseHas('experiences', [
            'id' => $experience->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    public function cannot_update_others_experience(): void
    {
        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $response = $this->actingAs($this->user) // Not the host
            ->putJson("/api/v1/experiences/{$experience->id}", $updateData);

        $response->assertStatus(403);
    }

    #[Test]
    public function can_delete_own_experience(): void
    {
        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->host)
            ->deleteJson("/api/v1/experiences/{$experience->id}");

        $response->assertStatus(200);

        $this->assertSoftDeleted('experiences', [
            'id' => $experience->id,
        ]);
    }

    #[Test]
    public function cannot_delete_others_experience(): void
    {
        $experience = Experience::factory()->create([
            'host_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->user) // Not the host
            ->deleteJson("/api/v1/experiences/{$experience->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function can_filter_experiences_by_location(): void
    {
        // Create experiences in different locations
        Experience::factory()->create([
            'host_id' => $this->host->id,
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Kyiv',
                'latitude' => 50.4501,
                'longitude' => 30.5234,
            ],
        ]);

        Experience::factory()->create([
            'host_id' => $this->host->id,
            'location' => [
                'country' => 'Ukraine',
                'city' => 'Lviv',
                'latitude' => 49.8397,
                'longitude' => 24.0297,
            ],
        ]);

        $response = $this->getJson('/api/v1/experiences?city=Kyiv');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_filter_experiences_by_category(): void
    {
        // Create experiences with different categories
        Experience::factory()->create([
            'host_id' => $this->host->id,
            'category' => ExperienceType::FOOD_AND_DRINK,
        ]);

        Experience::factory()->create([
            'host_id' => $this->host->id,
            'category' => ExperienceType::ARTS_AND_CULTURE,
        ]);

        $response = $this->getJson('/api/v1/experiences?category='.ExperienceType::FOOD_AND_DRINK->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_filter_experiences_by_language(): void
    {
        // Create experiences with different languages
        Experience::factory()->create([
            'host_id' => $this->host->id,
            'languages' => [Language::ENGLISH->value],
        ]);

        Experience::factory()->create([
            'host_id' => $this->host->id,
            'languages' => [Language::UKRAINIAN->value],
        ]);

        $response = $this->getJson('/api/v1/experiences?language='.Language::ENGLISH->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_filter_experiences_by_price_range(): void
    {
        // Create experiences with different prices
        $pricing1 = new Pricing(
            currency: 'USD',
            pricePerPerson: money(5000, 'USD'), // 50.00
            privateGroupMinPrice: money(20000, 'USD'),
        );

        $pricing2 = new Pricing(
            currency: 'USD',
            pricePerPerson: money(15000, 'USD'), // 150.00
            privateGroupMinPrice: money(50000, 'USD'),
        );

        Experience::factory()->create([
            'host_id' => $this->host->id,
            'pricing' => $pricing1,
        ]);

        Experience::factory()->create([
            'host_id' => $this->host->id,
            'pricing' => $pricing2,
        ]);

        $response = $this->getJson('/api/v1/experiences?price_min=10000&price_max=20000');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    #[Test]
    public function can_get_host_experiences(): void
    {
        // Create experiences for the host
        Experience::factory()->count(3)->create([
            'host_id' => $this->host->id,
        ]);

        // Create experiences for another host
        Experience::factory()->count(2)->create();

        $response = $this->getJson("/api/v1/hosts/{$this->host->id}/experiences");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->host = User::factory()->create();
    }
}
