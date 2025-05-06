<?php

namespace Database\Factories;

use App\Actions\Photo\CreatePhoto;
use App\Enums\Amenity;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\NoticeType;
use App\Enums\PropertyType;
use App\Models\Listing;
use App\Models\User;
use App\ValueObjects\Address;
use App\ValueObjects\Coordinates;
use App\ValueObjects\Listing\AcceptGuest;
use App\ValueObjects\Listing\AccessibilityFeature;
use App\ValueObjects\Listing\AvailabilitySetting;
use App\ValueObjects\Listing\Description;
use App\ValueObjects\Listing\Discount;
use App\ValueObjects\Listing\GuestSafety;
use App\ValueObjects\Listing\HouseRule;
use App\ValueObjects\Listing\RoomRule;
use App\ValueObjects\Location;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Random\RandomException;
use Symfony\Component\Uid\Ulid;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Listing::class;

    /**
     * @throws RandomException
     */
    public function definition(): array
    {
        $title = $this->faker->words(2, true);
        $slug = Str::slug($title).'-'.Str::random(5);
        $type = $this->faker->randomElement(PropertyType::cases());
        $user = User::query()->inRandomOrder()->first();

        return [
            'title' => $title,
            'slug' => $slug,
            'description' => $this->generateDescription(),
            'type' => $type,
            'subtype' => $this->getSubType($type),
            'listing_type' => $this->faker->randomElement(ListingType::cases()),
            'price_per_night' => Money::USD($this->faker->numberBetween(1000, 10000)),
            'discounts' => $this->generateDiscount(),
            'accept_guests' => $this->generateAcceptGuests(),
            'rooms_rules' => $this->generateRoomsRules(),
            'amenities' => $this->generateAmenities(),
            'accessibility_features' => $this->generateAccessibilityFeatures(),
            'advance_notice_type' => $this->faker->randomElement(NoticeType::all()),
            'availability_settings' => $this->generateAvailabilitySettings(),
            'location' => $this->generateLocation(),
            'house_rules' => $this->generateHouseRules(),
            'guest_safety' => $this->generateGuestSafety(),
            'host_id' => $user->id ?? Ulid::generate(),
            'is_published' => $this->faker->boolean(80),
            'is_featured' => $this->faker->boolean(20),
            'views_count' => $this->faker->numberBetween(0, 1000),
            'rating' => $this->faker->randomFloat(1, 0, 5),
            'reviews_count' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(ListingStatus::cases()),
        ];
    }

    private function generateDescription(): Description
    {
        return new Description(
            $this->faker->sentence(),
            $this->faker->paragraph(),
            $this->faker->sentence(),
            $this->faker->paragraph(),
            $this->faker->sentence(),
        );
    }

    private function getSubType(PropertyType $type): string
    {
        return collect($type->getSubtypes())->random(1)->first();
    }

    private function generateDiscount(): Discount
    {
        return new Discount(
            $this->faker->randomFloat(2, 50, 500),
            $this->faker->randomFloat(2, 50, 500),
        );
    }

    private function generateAcceptGuests(): AcceptGuest
    {
        return new AcceptGuest(
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
        );
    }

    private function generateRoomsRules(): RoomRule
    {
        return new RoomRule(
            $this->faker->numberBetween(1, 5),
            $this->faker->numberBetween(1, 5),
            $this->faker->numberBetween(1900, 2022),
            $this->faker->numberBetween(100, 1000)
        );
    }

    /**
     * @throws RandomException
     */
    private function generateAmenities(): array
    {
        $categories = Amenity::cases();
        $selectedCategories = collect($categories)->random(random_int(3, 6));

        return $selectedCategories->mapWithKeys(function (Amenity $category) {
            $allAmenities = $category->getSubtypes();

            $selectedSubtypes = collect($allAmenities)
                ->random(random_int(2, min(5, count($allAmenities))))
                ->values()
                ->all();

            return [$category->value => $selectedSubtypes];
        })->toArray();
    }

    private function generateAccessibilityFeatures(): AccessibilityFeature
    {
        return new AccessibilityFeature(
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
        );
    }

    private function generateAvailabilitySettings(): AvailabilitySetting
    {
        return new AvailabilitySetting(
            $this->faker->numberBetween(1, 7),
            $this->faker->numberBetween(7, 30),
        );
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

    private function generateHouseRules(): HouseRule
    {
        return new HouseRule(
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->sentence(3),
        );
    }

    private function generateGuestSafety(): GuestSafety
    {
        return new GuestSafety(
            $this->faker->boolean(),
            $this->faker->boolean(),
            $this->faker->boolean(),
        );
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Listing $listing) {
            $createPhoto = app(CreatePhoto::class);
            for ($i = 0; $i < 3; $i++) {
                $createPhoto->execute($listing);
            }

            //            // Create a favorite only if there are other users besides the host
            //            $potentialFavoriter = User::query()
            //                ->where('id', '!=', $listing->host_id)
            //                ->inRandomOrder()
            //                ->first();
            //
            //            if ($potentialFavoriter) {
            //                $listing->favorites()->create([
            //                    'user_id' => $potentialFavoriter->id,
            //                    'favoriteable_id' => $listing->id,
            //                    'favoriteable_type' => Listing::class,
            //                    'added_at' => now(),
            //                ]);
            //            }
        });
    }
}
