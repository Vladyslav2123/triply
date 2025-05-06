<?php

namespace Tests\Unit\Models;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserProfileRelationshipTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function profile_is_created_when_user_is_created(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->profile);
        $this->assertInstanceOf(Profile::class, $user->profile);
        $this->assertEquals($user->id, $user->profile->user_id);
    }

    #[Test]
    public function user_can_access_profile_and_profile_can_access_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->profile);

        $profile = $user->profile;
        $this->assertNotNull($profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    #[Test]
    public function profile_has_correct_fields(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $this->assertNotNull($profile->id);
        $this->assertNotNull($profile->user_id);

        $this->assertIsString($profile->preferred_language);
        $this->assertIsString($profile->preferred_currency);
        $this->assertIsBool($profile->email_notifications);
        $this->assertIsBool($profile->sms_notifications);
        $this->assertIsBool($profile->is_verified);
    }

    #[Test]
    public function updating_user_does_not_update_profile(): void
    {
        $user = User::factory()->create([
            'phone' => '+380991234567',
        ]);

        $originalFirstName = $user->profile->first_name;

        $user->update([
            'phone' => '+380999999999',
        ]);

        $user->profile->refresh();

        $this->assertEquals($originalFirstName, $user->profile->first_name);
    }

    #[Test]
    public function updating_profile_does_not_update_user(): void
    {
        $user = User::factory()->create([
            'phone' => '+380991234567',
        ]);

        $originalPhone = $user->phone;

        $user->profile->update([
            'first_name' => 'Updated Name',
        ]);

        $user->refresh();

        $this->assertEquals($originalPhone, $user->phone);
    }

    #[Test]
    public function profile_is_created_only_once(): void
    {
        $user = User::factory()->create();

        $profileId = $user->profile->id;

        $profile = $user->getOrCreateProfile();

        $this->assertEquals($profileId, $profile->id);

        $this->assertEquals(1, Profile::where('user_id', $user->id)->count());
    }

    #[Test]
    public function profile_has_correct_social_links(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $profile->update([
            'facebook_url' => 'https://facebook.com/testuser',
            'instagram_url' => 'https://instagram.com/testuser',
            'twitter_url' => 'https://twitter.com/testuser',
            'linkedin_url' => 'https://linkedin.com/in/testuser',
        ]);

        $profile->refresh();

        $this->assertEquals('https://facebook.com/testuser', $profile->facebook_url);
        $this->assertEquals('https://instagram.com/testuser', $profile->instagram_url);
        $this->assertEquals('https://twitter.com/testuser', $profile->twitter_url);
        $this->assertEquals('https://linkedin.com/in/testuser', $profile->linkedin_url);
    }

    #[Test]
    public function profile_can_be_verified(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $this->assertFalse($profile->is_verified);
        $this->assertNull($profile->verified_at);

        $profile->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verification_method' => 'manual',
        ]);

        $profile->refresh();

        $this->assertTrue($profile->is_verified);
        $this->assertNotNull($profile->verified_at);
        $this->assertEquals('manual', $profile->verification_method);
    }
}
