<?php

namespace Database\Factories;

use App\Actions\Photo\CreatePhoto;
use App\Enums\BookingDeadline;
use App\Enums\Drink;
use App\Enums\Equipment;
use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Food;
use App\Enums\Language;
use App\Enums\LocationType;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\Enums\Ticket;
use App\Enums\Transport;
use App\Models\Experience;
use App\Models\User;
use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Experience\BookingRules;
use App\ValueObjects\Experience\CancellationPolicy;
use App\ValueObjects\Experience\GroupDiscount;
use App\ValueObjects\Experience\GroupDiscounts;
use App\ValueObjects\Experience\GroupSize;
use App\ValueObjects\Experience\GuestNeeds;
use App\ValueObjects\Experience\GuestRequirements;
use App\ValueObjects\Experience\HostBio;
use App\ValueObjects\Experience\HostLicenses;
use App\ValueObjects\Experience\HostProvides;
use App\ValueObjects\Experience\HostVerification;
use App\ValueObjects\Experience\Pricing;
use App\ValueObjects\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Symfony\Component\Uid\Ulid;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(2, true);
        $slug = Str::slug($title).'-'.Str::random(5);
        $category = $this->faker->randomElement(ExperienceType::cases());
        $type = $this->faker->randomElement(LocationType::cases());
        $user = User::query()->inRandomOrder()->first();
        $languages = fake()->randomElements(Language::all(), 3);

        return [
            'host_id' => $user->id ?? Ulid::generate(),
            'slug' => $slug,
            'title' => $title,
            'is_published' => $this->faker->boolean(70),
            'is_featured' => $this->faker->boolean(20),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'reviews_count' => $this->faker->numberBetween(0, 100),
            'location' => $this->generateLocation(),
            'languages' => $languages,
            'sub_category' => $this->getSubCategory($category),
            'reviews' => $this->faker->paragraph(1),
            'description' => $this->faker->paragraph(1),
            'duration' => $this->faker->dateTimeBetween('now', '+2 hours'),
            'location_note' => $this->faker->sentence(),
            'location_subtype' => $this->getLocationSubType($type),
            'host_bio' => $this->generateHostBio(),
            'address' => $this->generateLocation(),
            'host_provides' => $this->generateHostProvides(),
            'guest_needs' => $this->generateGuestNeeds(),
            'guest_requirements' => $this->generateGuestRequirements(),
            'name' => $this->faker->word(),
            'grouping' => $this->generateGroupSize(),
            'starts_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'pricing' => $this->generatePricing(),
            'discounts' => $this->generateGroupDiscounts(),
            'booking_rules' => $this->generateBookingRules(),
            'cancellation_policy' => $this->generateCancellationPolicy(),
            'category' => $category,
            'location_type' => $type,
            'status' => $this->faker->randomElement(ExperienceStatus::all()),
            'host_verification' => new HostVerification(
                is_verified: $this->faker->boolean(30),
                is_phone_verified: $this->faker->boolean(50),
                is_identity_verified: $this->faker->boolean(20),
                verification_status: $this->faker->randomElement(['pending', 'approved', 'rejected']),
                verification_notes: $this->faker->optional()->sentence(),
                verification_documents: $this->faker->optional()->uuid(),
                verified_at: $this->faker->optional()->dateTime(),
            ),
            'host_licenses' => $this->generateHostLicenses(),
        ];
    }

    private function generateLocation(): Location
    {
        $address = new Address(
            $this->faker->streetName(),
            $this->faker->city(),
            $this->faker->postcode(),
            $this->faker->country(),
            $this->faker->randomElement(['California', 'New York', 'Texas', 'Florida', 'Illinois']),
        );

        $coordinates = new Coordinates(
            $this->faker->latitude(47.8, 48.8),
            $this->faker->longitude(22.0, 24.5)
        );

        return new Location($address, $coordinates);
    }

    private function getSubCategory(ExperienceType $type): string
    {
        return collect($type->getSubtypes())->random(1)->first();
    }

    private function getLocationSubType(LocationType $type): string
    {
        return collect($type->getSubtypes())->random(1)->first();
    }

    private function generateHostBio(): HostBio
    {
        return new HostBio(
            is_team_based: $this->faker->boolean(),
            about: $this->faker->paragraph(1),
        );
    }

    private function generateHostProvides(): HostProvides
    {
        return new HostProvides(
            includes_vehicle: $this->faker->boolean(),
            includes_boat: $this->faker->boolean(),
            includes_motorcycle: $this->faker->boolean(),
            includes_air_transport: $this->faker->boolean(),
            includes_none: $this->faker->boolean(),
            food: $this->faker->randomElements(Food::cases()),
            drink: $this->faker->randomElements(Drink::cases()),
            ticket: $this->faker->randomElements(Ticket::cases()),
            transport: $this->faker->randomElements(Transport::cases()),
            equipment: $this->faker->randomElements(Equipment::cases()),
        );
    }

    private function generateGuestNeeds(): GuestNeeds
    {
        $enabled = $this->faker->boolean();
        $items = [];

        if ($enabled) {
            $items = [
                $this->faker->paragraph(1),
                $this->faker->optional(0.5)->paragraph(1),
                $this->faker->optional(0.3)->paragraph(1),
            ];

            $items = array_filter($items);
        }

        return new GuestNeeds(
            enabled: $enabled,
            items: $items,
        );
    }

    private function generateGuestRequirements(): GuestRequirements
    {
        return new GuestRequirements(
            minimum_age: $this->faker->optional()->numberBetween(6, 18),
            can_bring_children_under_2: $this->faker->boolean(30),
            accessibility_communication: $this->faker->boolean(20),
            accessibility_mobility: $this->faker->boolean(20),
            accessibility_sensory: $this->faker->boolean(20),
            physical_activity_level: $this->faker->optional()->randomElement(PhysicalActivityLevel::cases()),
            skill_level: $this->faker->optional()->randomElement(SkillLevel::cases()),
            additional_requirements: $this->faker->optional()->sentence(),
        );
    }

    private function generateGroupSize(): GroupSize
    {
        return new GroupSize(
            generalGroupMax: $this->faker->numberBetween(1, 10),
            privateGroupMax: $this->faker->numberBetween(1, 10),
        );
    }

    private function generatePricing(): Pricing
    {
        $currency = 'USD';

        return new Pricing(
            currency: $currency,
            pricePerPerson: money($this->faker->randomFloat(2, 10, 100), $currency),
            privateGroupMinPrice: money($this->faker->randomFloat(2, 10, 100), $currency),
            requireMinimumPrice: $this->faker->boolean(),
            accessibleGuestsAllowed: $this->faker->boolean(),
        );
    }

    private function generateGroupDiscounts(): GroupDiscounts
    {
        $currency = 'USD';
        $min = 2;

        return new GroupDiscounts(
            [
                new GroupDiscount(
                    min: $min,
                    max: $this->faker->numberBetween($min, 10),
                    discount: $this->faker->numberBetween(0, 100),
                    currency: $currency,
                    pricePerPerson: money($this->faker->randomFloat(2, 10, 100), $currency),
                ),
                new GroupDiscount(
                    min: $min,
                    max: $this->faker->numberBetween($min, 10),
                    discount: $this->faker->numberBetween(0, 100),
                    currency: $currency,
                    pricePerPerson: money($this->faker->randomFloat(2, 10, 100), $currency),
                ),
            ]
        );
    }

    private function generateBookingRules(): BookingRules
    {
        return new BookingRules(
            firstGuestDeadline: $this->faker->randomElement(BookingDeadline::cases()),
            additionalGuestsDeadline: $this->faker->randomElement(BookingDeadline::cases()),
        );
    }

    private function generateCancellationPolicy(): CancellationPolicy
    {
        return new CancellationPolicy(
            week: $this->faker->boolean(),
            oneDay: $this->faker->boolean(),
        );
    }

    private function generateHostLicenses(): HostLicenses
    {
        return new HostLicenses(
            has_local_law_knowledge: $this->faker->boolean(80),
            has_guide_license: $this->faker->boolean(60),
            accepts_host_standards: $this->faker->boolean(90),
            accepts_compensation_rules: $this->faker->boolean(90),
            license_number: $this->faker->optional(0.7)->numerify('LIC-####-####'),
            license_type: $this->faker->optional(0.7)->randomElement(['guide', 'tour_operator', 'local_business']),
            license_issuer: $this->faker->optional(0.7)->company(),
            license_expiry: $this->faker->optional(0.7)->dateTimeBetween('+1 month', '+2 years'),
            additional_documents: $this->faker->optional(0.5)->words(3),
        );
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Experience $experience) {
            $createPhoto = app(CreatePhoto::class);
            for ($i = 0; $i < 3; $i++) {
                $createPhoto->execute($experience);
            }

            //            // Create a favorite only if there are other users besides the host
            //            $potentialFavoriter = User::query()
            //                ->where('id', '!=', $experience->host_id)
            //                ->inRandomOrder()
            //                ->first();
            //
            //            if ($potentialFavoriter) {
            //                $experience->favorites()->create([
            //                    'user_id' => $potentialFavoriter->id,
            //                    'favoriteable_id' => $experience->id,
            //                    'favoriteable_type' => Experience::class,
            //                    'added_at' => now(),
            //                ]);
            //            }
        });
    }
}
