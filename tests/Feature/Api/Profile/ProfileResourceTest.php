<?php

namespace Tests\Feature\Api\Profile;

use App\Enums\Gender;
use App\Enums\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_own_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',

                'birth_date',
                'gender',
                'email_notifications',
                'sms_notifications',
                'preferred_language',
                'preferred_currency',
            ]);
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/v1/profile');

        $response->assertStatus(401);
    }

    public function test_user_can_update_profile(): void
    {
        $profileData = [
            'first_name' => 'Updated First',
            'last_name' => 'Updated Last',
            'birth_date' => '1990-01-01',
            'gender' => Gender::MALE->value,
            'about' => 'This is my updated bio',
            'languages' => [Language::ENGLISH->value, Language::UKRAINIAN->value],
            'work' => 'Software Developer',
            'company' => 'Tech Corp',
            'location' => json_encode(['address' => 'Kyiv, Ukraine']),
            'phone' => '+380991234567', // This will be passed to the User model
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/profile', $profileData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        // Check that profile was updated
        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'first_name' => 'Updated First',
            'last_name' => 'Updated Last',
            'work' => 'Software Developer',
            'company' => 'Tech Corp',
        ]);

        // Check that user was also updated with synced data
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'phone' => '+380991234567',
        ]);
    }

    public function test_user_can_update_profile_notifications(): void
    {
        $notificationSettings = [
            'email_notifications' => true,
            'sms_notifications' => false,
        ];

        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/notifications', $notificationSettings);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'email_notifications' => true,
            'sms_notifications' => false,
        ]);
    }

    public function test_user_can_update_profile_preferences(): void
    {
        $preferences = [
            'preferred_language' => 'uk',
            'preferred_currency' => 'UAH',
        ];

        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/preferences', $preferences);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'preferred_language' => 'uk',
            'preferred_currency' => 'UAH',
        ]);
    }

    public function test_user_can_verify_profile(): void
    {
        $verificationData = [
            'verification_method' => 'id_card',
        ];

        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/verify', $verificationData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'is_verified' => true,
            'verification_method' => 'id_card',
        ]);
    }

    public function test_user_can_delete_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->deleteJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        // Profile should be soft deleted
        $this->assertSoftDeleted('profiles', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_profile_validation_errors_are_returned(): void
    {
        $invalidData = [
            'first_name' => '', // Empty when required
            'phone' => 'not-a-phone-number', // Invalid format
            'gender' => 'invalid-gender', // Not in enum
        ];

        $response = $this->actingAs($this->user)
            ->putJson('/api/v1/profile', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'phone', 'gender']);
    }

    public function test_user_gets_404_when_profile_not_found(): void
    {
        // Create a user without a profile
        $userWithoutProfile = User::factory()->create();

        // Delete the profile if it was automatically created
        if ($userWithoutProfile->profile) {
            $userWithoutProfile->profile->delete();
            $userWithoutProfile->refresh();
        }

        $response = $this->actingAs($userWithoutProfile)
            ->getJson('/api/v1/profile');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Профіль не знайдено',
            ]);
    }

    public function test_notification_settings_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/notifications', [
                'email_notifications' => 'not-a-boolean',
                'sms_notifications' => 'not-a-boolean',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_notifications', 'sms_notifications']);
    }

    public function test_preferences_validation_works(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/preferences', [
                'preferred_language' => '',
                'preferred_currency' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preferred_language', 'preferred_currency']);
    }

    public function test_preferences_are_properly_formatted(): void
    {
        // Prepare data with different formatting
        $preferences = [
            'preferred_language' => 'EN', // Uppercase
            'preferred_currency' => 'usd', // Lowercase
        ];

        // Update preferences
        $response = $this->actingAs($this->user)
            ->patchJson('/api/v1/profile/preferences', $preferences);

        // Check success
        $response->assertStatus(200);

        // Check that data was properly formatted in the database
        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->user->id,
            'preferred_language' => 'en', // Should be converted to lowercase
            'preferred_currency' => 'USD', // Should be converted to uppercase
        ]);

        // Check that response contains properly formatted data
        $response->assertJson([
            'preferences' => [
                'language' => 'en',
                'currency' => 'USD',
            ],
        ]);
    }
}
