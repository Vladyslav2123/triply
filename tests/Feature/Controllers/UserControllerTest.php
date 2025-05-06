<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;

class UserControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    /**
     * Тест отримання списку користувачів (тільки для адміна)
     */
    #[Test]
    public function index_returns_paginated_users_for_admin(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'email',
                        'role',
                        'created_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    /**
     * Тест заборони отримання списку користувачів для звичайного користувача
     */
    #[Test]
    public function index_forbidden_for_regular_user(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    /**
     * Тест отримання даних конкретного користувача
     */
    #[Test]
    public function show_returns_user_details(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/users/{$this->user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'email',
                    'role',
                    'profile',
                    'created_at',
                ],
            ])
            ->assertJsonPath('data.id', $this->user->id);
    }

    /**
     * Тест заборони перегляду даних іншого користувача
     */
    #[Test]
    public function show_forbidden_for_other_users_data(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/v1/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    /**
     * Тест дозволу адміну переглядати дані будь-якого користувача
     */
    #[Test]
    public function admin_can_view_any_user(): void
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/users/{$otherUser->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $otherUser->id);
    }

    /**
     * Тест отримання списку користувачів з фільтрацією
     */
    #[Test]
    public function index_filters_users_by_role(): void
    {
        User::factory()->count(3)->create(['role' => 'host']);
        User::factory()->count(2)->create(['role' => 'guest']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users?role=host');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Тест отримання списку користувачів з пошуком
     */
    #[Test]
    public function index_searches_users_by_email_or_phone(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        User::factory()->create([
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users?search=jane@example');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Тест отримання списку користувачів з сортуванням
     */
    #[Test]
    public function index_sorts_users(): void
    {
        User::factory()->create([
            'created_at' => now()->subDays(10),
        ]);

        User::factory()->create([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users?sort=created_at');

        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/users?sort=-created_at');

        $response->assertStatus(200);
    }
}
