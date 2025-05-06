<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use App\Services\OTPService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OtpAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_request_otp(): void
    {
        $response = $this->postJson('/api/v1/send-otp', [
            'phone' => '+380991234567',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function user_can_register_with_valid_otp(): void
    {
        $userData = [
            'phone' => '+380991234567',
            'otp' => '123456',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        $response = $this->postJson('/api/v1/auth/register-otp', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                    'phone',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'phone' => '+380991234567',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $response->json('user.id'),
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    #[Test]
    public function user_cannot_register_with_invalid_otp(): void
    {
        $userData = [
            'phone' => '+380991234567',
            'otp' => '654321',
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
        ];

        $response = $this->postJson('/api/v1/auth/register-otp', $userData);

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseMissing('users', [
            'email' => 'john.doe@example.com',
        ]);
    }

    #[Test]
    public function user_can_login_with_valid_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '+380991234567',
        ]);

        $response = $this->postJson('/api/v1/auth/login-otp', [
            'phone' => '+380991234567',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'email',
                ],
            ])
            ->assertJsonPath('user.id', $user->id);
    }

    #[Test]
    public function user_cannot_login_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '+380991234567',
        ]);

        $response = $this->postJson('/api/v1/auth/login-otp', [
            'phone' => '+380991234567',
            'otp' => '654321',
        ]);

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }

    #[Test]
    public function user_cannot_login_with_nonexistent_phone(): void
    {
        $response = $this->postJson('/api/v1/auth/login-otp', [
            'phone' => '+380999999999',
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    #[Test]
    public function otp_validation_errors_are_returned(): void
    {
        $response = $this->postJson('/api/v1/send-otp', [
            'phone' => 'not-a-phone-number',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(OTPService::class, function ($mock) {
            $mock->shouldReceive('verifyOTP')
                ->with('+380991234567', '123456')
                ->andReturn(true);

            $mock->shouldReceive('verifyOTP')
                ->with('+380991234567', '654321')
                ->andReturn(false);

            $mock->shouldReceive('generateAndSendOtp')
                ->andReturn(true);
        });
    }
}
