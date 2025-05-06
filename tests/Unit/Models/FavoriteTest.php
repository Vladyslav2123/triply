<?php

namespace Tests\Unit\Models;

use App\Models\Experience;
use App\Models\Favorite;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $listing = Listing::factory()->create();
        $favorite = Favorite::factory()->forListing($listing)->create();

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $favorite->id);
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $favorite = new Favorite;

        $this->assertEquals([
            'added_at' => 'datetime:Y-m-d',
        ], $favorite->getCasts());
    }

    #[Test]
    public function it_has_user_relationship(): void
    {
        $favorite = new Favorite;
        $this->assertInstanceOf(BelongsTo::class, $favorite->user());
        $this->assertEquals(User::class, $favorite->user()->getRelated()::class);

        $user = User::factory()->create();
        $listing = Listing::factory()->create();
        $favorite = Favorite::factory()->forListing($listing)->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $favorite->user);
        $this->assertEquals($user->id, $favorite->user->id);
    }

    #[Test]
    public function it_has_favoriteable_relationship(): void
    {
        $favorite = new Favorite;
        $this->assertInstanceOf(MorphTo::class, $favorite->favoriteable());

        $listing = Listing::factory()->create();
        $favoriteListing = Favorite::factory()->forListing($listing)->create();

        $this->assertInstanceOf(Listing::class, $favoriteListing->favoriteable);
        $this->assertEquals($listing->id, $favoriteListing->favoriteable->id);

        $experience = Experience::factory()->create();
        $favoriteExperience = Favorite::factory()->forExperience($experience)->create();

        $this->assertInstanceOf(Experience::class, $favoriteExperience->favoriteable);
        $this->assertEquals($experience->id, $favoriteExperience->favoriteable->id);
    }

    #[Test]
    public function it_does_not_use_timestamps(): void
    {
        $favorite = new Favorite;

        $this->assertFalse($favorite->timestamps);
    }

    #[Test]
    public function factory_requires_favoriteable_type_and_id(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Favoriteable type and ID must be set');

        Favorite::factory()->create();
    }

    #[Test]
    public function factory_creates_valid_favorite_for_listing(): void
    {
        $listing = Listing::factory()->create();
        $favorite = Favorite::factory()->forListing($listing)->create();

        $this->assertNotNull($favorite->id);
        $this->assertNotNull($favorite->user_id);
        $this->assertEquals($listing->id, $favorite->favoriteable_id);
        $this->assertEquals(Listing::class, Relation::getMorphedModel($favorite->favoriteable_type));
        $this->assertNotNull($favorite->added_at);
        $this->assertNotEquals($listing->host_id, $favorite->user_id);
    }

    #[Test]
    public function factory_creates_valid_favorite_for_experience(): void
    {
        $experience = Experience::factory()->create();
        $favorite = Favorite::factory()->forExperience($experience)->create();

        $type = Relation::getMorphedModel($favorite->favoriteable_type);

        $this->assertNotNull($favorite->id);
        $this->assertNotNull($favorite->user_id);
        $this->assertEquals($experience->id, $favorite->favoriteable_id);
        $this->assertEquals(Experience::class, $type);
        $this->assertNotNull($favorite->added_at);
        $this->assertNotEquals($experience->host_id, $favorite->user_id);
    }

    #[Test]
    public function it_enforces_unique_user_favoriteable_combination(): void
    {
        $listing = Listing::factory()->create();
        $favorite = Favorite::factory()->forListing($listing)->create();

        $this->expectException(QueryException::class);

        Favorite::factory()->forListing($listing)->create([
            'user_id' => $favorite->user_id,
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->count(3)->create();
    }
}
