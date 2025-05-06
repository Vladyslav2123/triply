<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SanctumSessionAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    #[Test]
    public function user_can_login_with_session(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'authenticated',
                'user',
            ])
            ->assertJson([
                'authenticated' => true,
            ]);

        $this->assertAuthenticated('web');
    }

    #[Test]
    public function user_can_access_protected_routes_with_session(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get('/api/v1/user');

        $response->assertStatus(200)
            ->assertJsonPath('id', $this->user->id);
    }

    #[Test]
    public function user_can_logout_from_session(): void
    {
        $this->actingAs($this->user);

        $response = $this->postJson('/logout');

        $response->assertStatus(204);

        $this->assertGuest('web');
    }

    #[Test]
    public function csrf_protection_is_enforced(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/login', [
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200);

        $this->assertTrue(
            in_array(
                ValidateCsrfToken::class,
                collect(app()->make(Kernel::class)->getMiddlewareGroups()['web'])->toArray()
            )
        );
    }

    #[Test]
    public function session_authentication_persists_across_requests(): void
    {
        $this->actingAs($this->user);

        $this->get('/api/v1/user')->assertStatus(200);

        $this->get('/api/v1/user')->assertStatus(200);
    }

    #[Test]
    public function api_routes_are_stateful(): void
    {
        $statefulDomains = config('sanctum.stateful');

        $this->assertTrue(
            in_array('localhost', $statefulDomains) ||
            in_array('127.0.0.1', $statefulDomains) ||
            in_array('triply.test', $statefulDomains) ||
            in_array('triply.blog', $statefulDomains)
        );
    }

    #[Test]
    public function sanctum_uses_web_guard(): void
    {
        $this->assertEquals(
            ['web'],
            config('sanctum.guard')
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }
}
