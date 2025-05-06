<?php

namespace Tests\Unit\Models;

use App\Casts\ExperienceTypeCast;
use App\Casts\LocationTypeCast;
use App\Enums\ExperienceStatus;
use App\Enums\Language;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\Models\Experience;
use App\Models\ExperienceAvailability;
use App\Models\Favorite;
use App\Models\Photo;
use App\Models\Reservation;
use App\Models\User;
use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Experience\GroupSize;
use App\ValueObjects\Experience\GuestRequirements;
use App\ValueObjects\Experience\HostBio;
use App\ValueObjects\Experience\Pricing;
use App\ValueObjects\Location;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_uses_ulids_for_ids(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{26}$/', $experience->id);
    }

    #[Test]
    public function it_has_host_relationship(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $experience->host);
        $this->assertEquals($user->id, $experience->host->id);
    }

    #[Test]
    public function it_has_photos_relationship(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $photo = Photo::factory()->create([
            'photoable_id' => $experience->id,
            'photoable_type' => 'experience',
        ]);

        $experience->refresh();

        $this->assertInstanceOf(Collection::class, $experience->photos);
        $this->assertInstanceOf(Photo::class, $experience->photos->last());

        $actualPhotoId = $experience->photos->last()->id;

        $this->assertEquals($photo->id, $actualPhotoId,
            "Expected photo ID {$photo->id} but got {$actualPhotoId}");
    }

    #[Test]
    public function it_has_availability_relationship(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $availability = ExperienceAvailability::factory()->forExperience($experience)->create();

        $experience->refresh();

        $this->assertInstanceOf(Collection::class, $experience->availability);
        $this->assertInstanceOf(ExperienceAvailability::class, $experience->availability->first());

        $actualAvailabilityId = $experience->availability->first()->id;

        $this->assertEquals($availability->id, $actualAvailabilityId,
            "Expected availability ID {$availability->id} but got {$actualAvailabilityId}");
    }

    #[Test]
    public function it_has_reservations_relationship(): void
    {
        $user = User::factory()->create();
        $guest = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $reservation = Reservation::factory()->create([
            'guest_id' => $guest->id,
            'reservationable_id' => $experience->id,
            'reservationable_type' => 'experience',
        ]);

        // Refresh the experience to ensure we get the latest data
        $experience->refresh();

        $this->assertInstanceOf(Collection::class, $experience->reservations);
        $this->assertInstanceOf(Reservation::class, $experience->reservations->first());

        // Get the actual reservation ID from the relationship
        $actualReservationId = $experience->reservations->first()->id;

        // Compare with the expected ID
        $this->assertEquals($reservation->id, $actualReservationId,
            "Expected reservation ID {$reservation->id} but got {$actualReservationId}");
    }

    #[Test]
    public function it_has_favorites_relationship(): void
    {
        $user = User::factory()->create();
        $favoriteUser = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $favorite = Favorite::factory()->create([
            'user_id' => $favoriteUser->id,
            'favoriteable_id' => $experience->id,
            'favoriteable_type' => 'experience',
        ]);

        $experience->refresh();

        $this->assertInstanceOf(Collection::class, $experience->favorites);
        $this->assertInstanceOf(Favorite::class, $experience->favorites->first());

        $actualFavoriteId = $experience->favorites->first()->id;

        $this->assertEquals($favorite->id, $actualFavoriteId,
            "Expected favorite ID {$favorite->id} but got {$actualFavoriteId}");
    }

    #[Test]
    public function it_has_correct_casts(): void
    {
        $experience = new Experience;
        $casts = $experience->getCasts();

        $this->assertEquals('array', $casts['seo']);
        $this->assertEquals(AsEnumCollection::class.':'.Language::class, $casts['languages']);
        $this->assertEquals('datetime:d-m-Y', $casts['duration']);
        $this->assertEquals('datetime:d-m-Y', $casts['starts_at']);
        $this->assertEquals(ExperienceStatus::class, $casts['status']);
        $this->assertEquals(ExperienceTypeCast::class, $casts['category']);
        $this->assertEquals(LocationTypeCast::class, $casts['location_type']);
    }

    #[Test]
    public function it_generates_slug_on_creation(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
            'title' => 'Test Experience Title',
        ]);

        $this->assertNotEmpty($experience->slug);
    }

    #[Test]
    public function it_generates_seo_data_on_creation(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
            'title' => 'Test Experience Title',
            'description' => 'This is a test description for the experience',
        ]);

        $this->assertNotEmpty($experience->seo);
        $this->assertIsArray($experience->seo);
        $this->assertArrayHasKey('meta_title', $experience->seo);
        $this->assertArrayHasKey('meta_description', $experience->seo);
        $this->assertArrayHasKey('meta_keywords', $experience->seo);
    }

    #[Test]
    public function it_hides_timestamps(): void
    {
        $user = User::factory()->create();
        $experience = Experience::factory()->create([
            'host_id' => $user->id,
        ]);

        $array = $experience->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }

    #[Test]
    public function it_can_handle_value_objects(): void
    {
        $user = User::factory()->create();

        $location = new Location(
            new Address(
                street: 'Test Street',
                city: 'Kyiv',
                postalCode: '01001',
                country: 'Ukraine',
                state: 'Test State'
            ),
            new Coordinates(
                latitude: 50.4501,
                longitude: 30.5234
            ));

        $hostBio = new HostBio(
            is_team_based: false,
            about: 'About the host',
        );

        $guestRequirements = new GuestRequirements(
            minimum_age: 18,
            can_bring_children_under_2: true,
            accessibility_communication: false,
            accessibility_mobility: true,
            accessibility_sensory: false,
            physical_activity_level: PhysicalActivityLevel::MODERATE,
            skill_level: SkillLevel::BEGINNER,
            additional_requirements: 'No additional requirements'
        );

        $groupSize = new GroupSize(
            generalGroupMax: 10,
            privateGroupMax: 5
        );

        $pricing = new Pricing(
            currency: 'USD',
            pricePerPerson: money(5000, 'USD'),
            privateGroupMinPrice: money(20000, 'USD'),
            requireMinimumPrice: true,
            accessibleGuestsAllowed: true
        );

        $experience = Experience::factory()->create([
            'host_id' => $user->id,
            'location' => $location,
            'host_bio' => $hostBio,
            'guest_requirements' => $guestRequirements,
            'grouping' => $groupSize,
            'pricing' => $pricing,
        ]);

        $experience->refresh();

        $this->assertInstanceOf(Location::class, $experience->location);
        $this->assertEquals('Ukraine', $experience->location->address->country);
        $this->assertEquals('Kyiv', $experience->location->address->city);

        $this->assertInstanceOf(HostBio::class, $experience->host_bio);
        $this->assertEquals('About the host', $experience->host_bio->about);
        $this->assertFalse($experience->host_bio->is_team_based);

        $this->assertInstanceOf(GuestRequirements::class, $experience->guest_requirements);
        $this->assertEquals(18, $experience->guest_requirements->minimum_age);
        $this->assertEquals(SkillLevel::BEGINNER, $experience->guest_requirements->skill_level);
        $this->assertEquals(PhysicalActivityLevel::MODERATE, $experience->guest_requirements->physical_activity_level);

        $this->assertInstanceOf(GroupSize::class, $experience->grouping);
        $this->assertEquals(10, $experience->grouping->generalGroupMax);
        $this->assertEquals(5, $experience->grouping->privateGroupMax);

        $this->assertInstanceOf(Pricing::class, $experience->pricing);
        $this->assertEquals('USD', $experience->pricing->currency);
        $this->assertEquals(5000, $experience->pricing->pricePerPerson->getAmount());
    }

    #[Test]
    public function it_can_filter_by_status(): void
    {
        $user = User::factory()->create();

        $activeExperience = Experience::factory()->create([
            'host_id' => $user->id,
            'status' => ExperienceStatus::PENDING,
        ]);

        $draftExperience = Experience::factory()->create([
            'host_id' => $user->id,
            'status' => ExperienceStatus::DRAFT,
        ]);

        $activeExperiences = Experience::query()
            ->where('status', ExperienceStatus::PENDING)
            ->get();

        $this->assertCount(1, $activeExperiences);
        $this->assertEquals($activeExperience->id, $activeExperiences->first()->id);

        $draftExperiences = Experience::query()
            ->where('status', ExperienceStatus::DRAFT)
            ->get();

        $this->assertCount(1, $draftExperiences);
        $this->assertEquals($draftExperience->id, $draftExperiences->first()->id);
    }

    #[Test]
    public function it_can_filter_by_host(): void
    {
        $host1 = User::factory()->create();
        $host2 = User::factory()->create();

        $experience1 = Experience::factory()->create([
            'host_id' => $host1->id,
        ]);

        $experience2 = Experience::factory()->create([
            'host_id' => $host2->id,
        ]);

        $host1Experiences = Experience::query()
            ->where('host_id', $host1->id)
            ->get();

        $this->assertCount(1, $host1Experiences);
        $this->assertEquals($experience1->id, $host1Experiences->first()->id);

        $host2Experiences = Experience::query()
            ->where('host_id', $host2->id)
            ->get();

        $this->assertCount(1, $host2Experiences);
        $this->assertEquals($experience2->id, $host2Experiences->first()->id);
    }
}
