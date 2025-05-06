<?php

namespace Tests\Unit\Models;

use App\Actions\Photo\CreatePhoto;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_casts(): void
    {
        $profile = new Profile;
        $casts = $profile->getCasts();

        $this->assertEquals('date', $casts['birth_date']);
        $this->assertEquals('boolean', $casts['is_superhost']);
        $this->assertEquals('float', $casts['response_speed']);
        $this->assertEquals('integer', $casts['views_count']);
        $this->assertEquals('float', $casts['rating']);
        $this->assertEquals('integer', $casts['reviews_count']);
        $this->assertEquals(EducationLevel::class, $casts['education_level']);
        $this->assertEquals('boolean', $casts['travel_history']);
        $this->assertEquals('array', $casts['next_destinations']);
        $this->assertEquals(Gender::class, $casts['gender']);
        $this->assertEquals('boolean', $casts['birth_decade']);
        $this->assertStringContainsString('AsEnumCollection', $casts['languages']);
        $this->assertStringContainsString('AsEnumCollection', $casts['interests']);
        $this->assertEquals('boolean', $casts['email_notifications']);
        $this->assertEquals('boolean', $casts['sms_notifications']);
        $this->assertEquals('boolean', $casts['is_verified']);
        $this->assertEquals('datetime', $casts['verified_at']);
        $this->assertEquals('datetime', $casts['last_active_at']);
    }

    #[Test]
    public function it_has_user_relationship(): void
    {
        $profile = new Profile;
        $this->assertEquals(User::class, $profile->user()->getRelated()::class);
        $this->assertEquals('user_id', $profile->user()->getForeignKeyName());

        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $profile->user->id);
        $this->assertInstanceOf(User::class, $profile->user);
    }

    #[Test]
    public function it_returns_full_name(): void
    {
        $user = User::factory()
            ->has(Profile::factory([
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])->count(1))
            ->create();

        $this->assertEquals('John Doe', $user->profile->full_name);
        $this->assertEquals('John Doe', $user->full_name);
    }

    #[Test]
    public function it_returns_unnamed_user_when_name_is_empty(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => null,
            'last_name' => null,
        ]);

        $this->assertEquals('Unnamed User', $profile->full_name);
    }

    #[Test]
    public function it_returns_formatted_address(): void
    {
        $profile = new class extends Profile
        {
            public function getAddressFormattedAttribute(): string
            {
                return '123 Main St, New York, NY 10001';
            }
        };

        $this->assertEquals('123 Main St, New York, NY 10001', $profile->address_formatted);
    }

    #[Test]
    public function it_returns_dash_when_address_is_empty(): void
    {
        $profile = new class extends Profile
        {
            public function getAddressFormattedAttribute(): string
            {
                return '-';
            }
        };

        $this->assertEquals('-', $profile->address_formatted);
    }

    #[Test]
    public function it_returns_social_links(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'facebook_url' => 'https://facebook.com/johndoe',
            'instagram_url' => 'https://instagram.com/johndoe',
            'twitter_url' => 'https://twitter.com/johndoe',
            'linkedin_url' => 'https://linkedin.com/in/johndoe',
        ]);

        $expectedLinks = [
            'facebook' => 'https://facebook.com/johndoe',
            'instagram' => 'https://instagram.com/johndoe',
            'twitter' => 'https://twitter.com/johndoe',
            'linkedin' => 'https://linkedin.com/in/johndoe',
        ];

        $this->assertEquals($expectedLinks, $profile->social_links);
    }

    #[Test]
    public function it_returns_null_for_missing_social_links(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'facebook_url' => 'https://facebook.com/johndoe',
            'instagram_url' => null,
            'twitter_url' => null,
            'linkedin_url' => 'https://linkedin.com/in/johndoe',
        ]);

        $expectedLinks = [
            'facebook' => 'https://facebook.com/johndoe',
            'instagram' => null,
            'twitter' => null,
            'linkedin' => 'https://linkedin.com/in/johndoe',
        ];

        $this->assertEquals($expectedLinks, $profile->social_links);
    }

    #[Test]
    public function it_uses_soft_deletes(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $profile->delete();

        $this->assertNull(Profile::find($profile->id));

        $this->assertNotNull(Profile::withTrashed()->find($profile->id));
    }

    #[Test]
    public function it_uses_ulids_for_primary_key(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(26, strlen($profile->id));
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]{26}$/', $profile->id);
    }

    #[Test]
    public function it_hides_sensitive_attributes(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $array = $profile->toArray();

        $this->assertArrayNotHasKey('user_id', $array);
        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
        $this->assertArrayNotHasKey('deleted_at', $array);
    }

    #[Test]
    public function it_syncs_with_user_when_updated(): void
    {
        $user = User::factory()->create([
            'phone' => '+380991234567',
        ]);

        $user->profile->update([
            'first_name' => 'Updated',
            'last_name' => 'Profile',
        ]);

        $user->refresh();

        $this->assertEquals('Updated', $user->profile->first_name);
        $this->assertEquals('Profile', $user->profile->last_name);
    }

    #[Test]
    public function it_can_verify_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $this->assertFalse($profile->is_verified);
        $this->assertNull($profile->verified_at);
        $this->assertNull($profile->verification_method);

        $profile->verify('id_card');

        $profile->refresh();

        $this->assertTrue($profile->is_verified);
        $this->assertNotNull($profile->verified_at);
        $this->assertEquals('id_card', $profile->verification_method);
    }

    #[Test]
    public function it_can_unverified_profile(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $profile->verify('id_card');
        $profile->refresh();
        $this->assertTrue($profile->is_verified);

        $profile->unverify();
        $profile->refresh();

        $this->assertFalse($profile->is_verified);
        $this->assertNull($profile->verified_at);
        $this->assertNull($profile->verification_method);
    }

    #[Test]
    public function it_can_increment_views_count(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $profile->views_count = 5;
        $profile->save();

        $profile->incrementViewsCount();

        $profile->refresh();

        $this->assertEquals(6, $profile->views_count);
    }

    #[Test]
    public function it_can_update_last_active_timestamp(): void
    {
        $user = User::factory()->create();
        $profile = $user->getOrCreateProfile();

        $profile->last_active_at = null;
        $profile->save();

        $profile->updateLastActive();

        $profile->refresh();

        $this->assertNotNull($profile->last_active_at);
        $this->assertEqualsWithDelta(now()->timestamp, $profile->last_active_at->timestamp, 5);
    }

    #[Test]
    public function it_has_photo_relationship(): void
    {
        $profile = new Profile;
        $this->assertEquals('App\\Models\\Photo', $profile->photo()->getRelated()::class);
        $this->assertEquals('photoable_id', $profile->photo()->getForeignKeyName());
        $this->assertEquals('photoable_type', $profile->photo()->getMorphType());
    }

    #[Test]
    public function it_can_check_if_has_photo(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $this->assertFalse($profile->hasPhoto());

        $mockProfile = $this->getMockBuilder(Profile::class)
            ->onlyMethods(['hasPhoto'])
            ->getMock();
        $mockProfile->method('hasPhoto')->willReturn(true);

        $this->assertTrue($mockProfile->hasPhoto());
    }

    #[Test]
    public function it_returns_default_photo_url_when_no_photo(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $mockProfile = $this->getMockBuilder(Profile::class)
            ->onlyMethods(['getPhotoUrl', 'hasPhoto'])
            ->getMock();
        $mockProfile->method('hasPhoto')->willReturn(false);
        $mockProfile->method('getPhotoUrl')->willReturn(asset('images/default-avatar.png'));

        $this->assertEquals(asset('images/default-avatar.png'), $mockProfile->getPhotoUrl());
    }

    #[Test]
    public function it_creates_photo_when_using_factory(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $profile = $user->profile;

        $createPhoto = app(CreatePhoto::class);
        $photo = $createPhoto->execute($profile);

        $profile->refresh();

        $this->assertTrue($profile->hasPhoto());
        $this->assertNotNull($profile->photo);
        $this->assertInstanceOf('App\\Models\\Photo', $profile->photo);

        $this->assertEquals($profile->id, $profile->photo->photoable_id);
        $this->assertEquals('profile', $profile->photo->photoable_type);
    }
}
