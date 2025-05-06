<?php

namespace Tests\Feature\Api\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserExistsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_returns_true_when_user_exists_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);
    }

    #[Test]
    public function it_returns_true_when_user_exists_by_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+380123456789',
        ]);

        $response = $this->postJson('/api/v1/users/exists', [
            'phone' => '+380123456789',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);
    }

    #[Test]
    public function it_returns_false_when_user_does_not_exist(): void
    {
        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => false,
            ]);
    }

    #[Test]
    public function it_validates_that_either_email_or_phone_is_provided(): void
    {
        $response = $this->postJson('/api/v1/users/exists', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    #[Test]
    public function it_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_true_when_either_email_or_phone_matches(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone' => '+380123456789',
        ]);

        // Different email, same phone
        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'different@example.com',
            'phone' => '+380123456789',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);

        // Same email, different phone
        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'test@example.com',
            'phone' => '+380987654321',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => true,
            ]);

        // Both different
        $response = $this->postJson('/api/v1/users/exists', [
            'email' => 'different@example.com',
            'phone' => '+380987654321',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'exists' => false,
            ]);
    }
}
