<?php

namespace Tests\Unit\Models;

use App\Enums\UserRole;
use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_casts(): void
    {
        $user = new User;
        $casts = $user->getCasts();

        $this->assertEquals(UserRole::class, $casts['role']);
        $this->assertEquals('datetime:Y-m-d H:i:s', $casts['email_verified_at']);
        $this->assertEquals('boolean', $casts['is_banned']);
    }

    #[Test]
    public function it_has_listings_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Listing::class, $user->listings()->getRelated()::class);
        $this->assertEquals('host_id', $user->listings()->getForeignKeyName());
    }

    #[Test]
    public function it_has_experiences_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Experience::class, $user->experiences()->getRelated()::class);
        $this->assertEquals('host_id', $user->experiences()->getForeignKeyName());
    }

    #[Test]
    public function it_has_reservations_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Reservation::class, $user->reservations()->getRelated()::class);
        $this->assertEquals('guest_id', $user->reservations()->getForeignKeyName());
    }

    #[Test]
    public function it_has_reviews_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Review::class, $user->reviews()->getRelated()::class);
        $this->assertEquals('reviewer_id', $user->reviews()->getForeignKeyName());
    }

    #[Test]
    public function it_has_sent_messages_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Message::class, $user->sentMessages()->getRelated()::class);
        $this->assertEquals('sender_id', $user->sentMessages()->getForeignKeyName());
    }

    #[Test]
    public function it_has_received_messages_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Message::class, $user->receivedMessages()->getRelated()::class);
        $this->assertEquals('recipient_id', $user->receivedMessages()->getForeignKeyName());
    }

    #[Test]
    public function it_has_favorites_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Favorite::class, $user->favorites()->getRelated()::class);
        $this->assertEquals('user_id', $user->favorites()->getForeignKeyName());
    }

    #[Test]
    public function it_has_profile_relationship(): void
    {
        $user = new User;
        $this->assertEquals(Profile::class, $user->profile()->getRelated()::class);
        $this->assertEquals('user_id', $user->profile()->getForeignKeyName());
    }

    #[Test]
    public function it_uses_slug_for_route_key_name(): void
    {
        $user = new User;
        $this->assertEquals('slug', $user->getRouteKeyName());
    }

    #[Test]
    public function it_returns_full_name_from_profile(): void
    {
        $user = User::factory()->create();

        if ($user->profile) {
            $user->profile->delete();
        }
        $user->refresh();

        $this->assertEquals('Unnamed User', $user->full_name);

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $user->refresh();

        $this->assertEquals($profile->id, $user->profile->id);
        $this->assertEquals('John', $user->profile->first_name);
        $this->assertEquals('Doe', $user->profile->last_name);
        $this->assertEquals('John Doe', $user->profile->full_name);
        $this->assertEquals('John Doe', $user->full_name);
    }

    #[Test]
    public function it_can_check_if_user_is_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $guest = User::factory()->create(['role' => UserRole::GUEST]);
        $host = User::factory()->create(['role' => UserRole::HOST]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($guest->isAdmin());
        $this->assertFalse($host->isAdmin());
    }

    #[Test]
    public function it_creates_profile_when_user_is_created(): void
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
        ]);

        $this->assertNotNull($user->profile);
        $this->assertEquals($user->id, $user->profile->user_id);
    }

    #[Test]
    public function it_can_get_or_create_profile(): void
    {
        $user = User::factory()->create();

        if ($user->profile) {
            $user->profile->delete();
            $user->refresh();
        }

        $profile = $user->getOrCreateProfile();

        $this->assertNotNull($profile);
        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $profile->id);

        $user->refresh();

        $this->assertNotNull($user->profile);
        $this->assertEquals($profile->id, $user->profile->id);
    }

    #[Test]
    public function it_maintains_profile_relationship(): void
    {
        $user = User::factory()->create([
            'phone' => '1234567890',
        ]);

        if ($user->profile) {
            $user->profile->delete();
            $user->refresh();
        }

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Existing',
            'last_name' => 'User',
        ]);

        $user->refresh();

        $this->assertNotNull($user->profile);
        $this->assertEquals($profile->id, $user->profile->id);
        $this->assertEquals('Existing', $user->profile->first_name);
        $this->assertEquals('User', $user->profile->last_name);
        $this->assertEquals('Existing User', $user->full_name);
    }

    #[Test]
    public function it_uses_ulids_for_primary_key(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(26, strlen($user->id));
        $this->assertMatchesRegularExpression('/^[0-9a-zA-Z]{26}$/', $user->id);
    }

    #[Test]
    public function it_hides_sensitive_attributes(): void
    {
        $user = new User([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'remember_token' => 'token123',
        ]);

        $hiddenAttributes = $user->getHidden();

        $this->assertContains('password', $hiddenAttributes, 'Password should be hidden');
        $this->assertContains('remember_token', $hiddenAttributes, 'Remember token should be hidden');
        $this->assertContains('created_at', $hiddenAttributes, 'Created at should be hidden');
        $this->assertContains('updated_at', $hiddenAttributes, 'Updated at should be hidden');
    }

    #[Test]
    public function it_sets_default_role_when_creating(): void
    {
        $user = User::factory()->make();
        $user->role = null;
        $user->save();

        $this->assertEquals(UserRole::USER, $user->role);
    }

    #[Test]
    public function it_generates_slug_on_creation(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->slug);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{15}$/', $user->slug);
    }

    #[Test]
    public function it_updates_slug_when_email_changes(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $originalSlug = $user->slug;

        $user->update([
            'email' => 'jane@example.com',
        ]);

        $this->assertEquals($originalSlug, $user->fresh()->slug);
    }

    #[Test]
    public function it_creates_unique_slugs(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertNotEquals($user1->slug, $user2->slug);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{15}$/', $user1->slug);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{15}$/', $user2->slug);
    }

    #[Test]
    public function it_has_is_banned_field_defaulting_to_false(): void
    {
        $user = User::factory()->create();

        $this->assertFalse($user->is_banned);

        $user->is_banned = true;
        $user->save();
        $user->refresh();

        $this->assertTrue($user->is_banned);
    }

    #[Test]
    public function it_provides_profile_name_attributes(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $profile->refresh();
        $this->assertEquals('John', $profile->first_name);
        $this->assertEquals('Doe', $profile->last_name);
        $this->assertEquals('John Doe', $profile->full_name);
    }

    #[Test]
    public function it_stores_email_as_provided(): void
    {
        $email = 'TEST@EXAMPLE.COM';
        $user = User::factory()->create([
            'email' => $email,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $this->assertEquals($email, $user->email);

        $foundUser = User::where('email', $email)->first();
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }

    #[Test]
    public function it_has_profile_with_matching_user_id(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile;

        $this->assertNotNull($profile);
        $this->assertEquals($user->id, $profile->user_id);
    }

    #[Test]
    public function it_returns_url_attribute(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->url);
        $this->assertStringContainsString($user->slug, $user->url);
    }
}
