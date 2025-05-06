<?php

namespace Tests\Feature\Api\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_register_with_valid_data(): void
    {
        Event::fake();

        $userData = [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+380991234567',
            'birth_date' => '1990-01-01',
        ];

        $response = $this->postJson('/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'phone' => '+380991234567',
        ]);

        $user = User::where('email', 'john.doe@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }

    #[Test]
    public function user_cannot_register_with_invalid_data(): void
    {
        $response = $this->postJson('/register', [
            'name' => 'Jo',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    #[Test]
    public function user_cannot_register_with_duplicate_email(): void
    {
        $testEmail = 'existing.'.uniqid().'@example.com';

        User::factory()->create([
            'email' => $testEmail,
        ]);

        $response = $this->postJson('/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => $testEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+38099'.rand(1000000, 9999999),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function user_cannot_register_with_duplicate_phone(): void
    {
        $testPhone = '+38099'.rand(1000000, 9999999);

        User::factory()->create([
            'phone' => $testPhone,
        ]);

        $response = $this->postJson('/register', [
            'name' => 'John',
            'surname' => 'Doe',
            'email' => 'new.'.uniqid().'@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => $testPhone,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }
}
