<?php

namespace Tests\Feature\Controllers;

use App\Enums\Gender;
use App\Enums\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;

class ProfileControllerTest extends ApiControllerTestCase
{
    use WithFaker;

    private User $userWithProfile;

    /**
     * Тест отримання власного профілю
     */
    #[Test]
    public function show_returns_user_profile(): void
    {
        $response = $this->actingAs($this->userWithProfile)
            ->getJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',
                'phone',
                'birth_date',
                'gender',
                'about',
                'languages',
                'work',
                'company',
                'country',
                'city',
                'email_notifications',
                'sms_notifications',
                'preferred_language',
                'preferred_currency',
            ])
            ->assertJsonPath('id', $this->userWithProfile->profile->id);
    }

    /**
     * Тест оновлення профілю
     */
    #[Test]
    public function update_modifies_profile(): void
    {
        $profileData = [
            'first_name' => 'Updated First',
            'last_name' => 'Updated Last',
            'phone' => '+380991234567',
            'birth_date' => '1990-01-01',
            'gender' => Gender::MALE->value,
            'about' => 'This is my updated bio',
            'languages' => [Language::ENGLISH->value, Language::UKRAINIAN->value],
            'work' => 'Software Developer',
            'company' => 'Tech Corp',
            'country' => 'Ukraine',
            'city' => 'Kyiv',
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->putJson('/api/v1/profile', $profileData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->userWithProfile->id,
            'first_name' => 'Updated First',
            'last_name' => 'Updated Last',
            'phone' => '+380991234567',
            'work' => 'Software Developer',
            'company' => 'Tech Corp',
            'country' => 'Ukraine',
            'city' => 'Kyiv',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->userWithProfile->id,
            'name' => 'Updated First',
            'surname' => 'Updated Last',
            'phone' => '+380991234567',
        ]);
    }

    /**
     * Тест валідації при оновленні профілю
     */
    #[Test]
    public function update_validates_profile_data(): void
    {
        $invalidData = [
            'first_name' => '',
            'phone' => 'not-a-phone-number',
            'gender' => 'invalid-gender',
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->putJson('/api/v1/profile', $invalidData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'phone', 'gender']);
    }

    /**
     * Тест оновлення налаштувань сповіщень
     */
    #[Test]
    public function update_notifications_settings(): void
    {
        $notificationSettings = [
            'email_notifications' => true,
            'sms_notifications' => false,
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/notifications', $notificationSettings);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->userWithProfile->id,
            'email_notifications' => true,
            'sms_notifications' => false,
        ]);
    }

    /**
     * Тест оновлення налаштувань мови та валюти
     */
    #[Test]
    public function update_preferences(): void
    {
        $preferences = [
            'preferred_language' => 'uk',
            'preferred_currency' => 'UAH',
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/preferences', $preferences);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->userWithProfile->id,
            'preferred_language' => 'uk',
            'preferred_currency' => 'UAH',
        ]);
    }

    /**
     * Тест верифікації профілю
     */
    #[Test]
    public function verify_profile(): void
    {
        $verificationData = [
            'verification_method' => 'document',
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/verify', $verificationData);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->userWithProfile->id,
            'is_verified' => true,
            'verification_method' => 'id_card',
        ]);
    }

    /**
     * Тест завантаження фото профілю
     */
    #[Test]
    public function upload_profile_photo(): void
    {
        Storage::fake('s3');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($this->userWithProfile)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'photo']);

        $this->assertNotNull($this->userWithProfile->profile->fresh()->photo);
    }

    /**
     * Тест видалення фото профілю
     */
    #[Test]
    public function delete_profile_photo(): void
    {
        // Спочатку завантажуємо фото
        Storage::fake('s3');
        $file = UploadedFile::fake()->image('avatar.jpg');

        $this->actingAs($this->userWithProfile)
            ->postJson('/api/v1/profile/photo', [
                'photo' => $file,
            ]);

        // Оновлюємо модель користувача, щоб отримати актуальні дані
        $this->userWithProfile->refresh();

        // Переконуємося, що фото було завантажено
        $this->assertNotNull($this->userWithProfile->profile->photo);

        // Потім видаляємо його
        $response = $this->actingAs($this->userWithProfile)
            ->deleteJson('/api/v1/profile/photo');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        // Оновлюємо модель користувача, щоб отримати актуальні дані
        $this->userWithProfile->refresh();
        $this->assertNull($this->userWithProfile->profile->photo);
    }

    /**
     * Тест видалення профілю
     */
    #[Test]
    public function delete_profile(): void
    {
        $response = $this->actingAs($this->userWithProfile)
            ->deleteJson('/api/v1/profile');

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);

        $this->assertSoftDeleted('profiles', [
            'user_id' => $this->userWithProfile->id,
        ]);
    }

    /**
     * Тест перегляду профілю іншого користувача
     */
    #[Test]
    public function view_another_user_profile(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->profile->update([
            'first_name' => 'Other',
            'last_name' => 'User',
            'about' => 'This is another user profile',
        ]);

        $response = $this->actingAs($this->userWithProfile)
            ->getJson("/api/v1/users/{$otherUser->id}/profile");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'first_name',
                'last_name',
                'full_name',
                'about',
            ])
            ->assertJsonPath('first_name', 'Other')
            ->assertJsonPath('last_name', 'User');
    }

    /**
     * Тест інкрементування лічильника переглядів профілю
     */
    #[Test]
    public function viewing_profile_increments_view_count(): void
    {
        $otherUser = User::factory()->create();
        $initialViewCount = $otherUser->profile->views_count ?? 0;

        $response = $this->actingAs($this->userWithProfile)
            ->getJson("/api/v1/users/{$otherUser->id}/profile");

        $response->assertStatus(200);

        $otherUser->profile->refresh();
        $this->assertEquals($initialViewCount + 1, $otherUser->profile->views_count);
    }

    /**
     * Тест отримання 404 при спробі отримати неіснуючий профіль
     */
    #[Test]
    public function get404_when_profile_not_found(): void
    {
        $userWithoutProfile = User::factory()->create();

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

    /**
     * Тест валідації налаштувань сповіщень
     */
    #[Test]
    public function notification_settings_validation(): void
    {
        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/notifications', [
                'email_notifications' => 'not-a-boolean',
                'sms_notifications' => 'not-a-boolean',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_notifications', 'sms_notifications']);
    }

    /**
     * Тест валідації налаштувань мови та валюти
     */
    #[Test]
    public function preferences_validation(): void
    {
        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/preferences', [
                'preferred_language' => '',
                'preferred_currency' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['preferred_language', 'preferred_currency']);
    }

    /**
     * Тест правильного форматування налаштувань мови та валюти
     */
    #[Test]
    public function preferences_are_properly_formatted(): void
    {
        $preferences = [
            'preferred_language' => 'EN',
            'preferred_currency' => 'usd',
        ];

        $response = $this->actingAs($this->userWithProfile)
            ->patchJson('/api/v1/profile/preferences', $preferences);

        $response->assertStatus(200);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $this->userWithProfile->id,
            'preferred_language' => 'en',
            'preferred_currency' => 'USD',
        ]);

        $response->assertJson([
            'preferences' => [
                'language' => 'en',
                'currency' => 'USD',
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->userWithProfile = User::factory()->create();

        $this->userWithProfile->getOrCreateProfile();
        $this->userWithProfile->refresh();
    }
}
