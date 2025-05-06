<?php

namespace App\Models;

use App\Casts\AcceptGuestCast;
use App\Casts\AccessibilityFeatureCast;
use App\Casts\AvailabilitySettingCast;
use App\Casts\DescriptionCast;
use App\Casts\DiscountCast;
use App\Casts\GuestSafetyCast;
use App\Casts\HouseRuleCast;
use App\Casts\LocationCast;
use App\Casts\RoomRuleCast;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\NoticeType;
use App\Enums\PropertyType;
use App\Enums\ReservationStatus;
use App\Models\Traits\Concerns\HasSeo;
use App\ValueObjects\Listing\AcceptGuest;
use App\ValueObjects\Listing\AccessibilityFeature;
use App\ValueObjects\Listing\AvailabilitySetting;
use App\ValueObjects\Listing\Description;
use App\ValueObjects\Listing\Discount;
use App\ValueObjects\Listing\GuestSafety;
use App\ValueObjects\Listing\HouseRule;
use App\ValueObjects\Listing\RoomRule;
use App\ValueObjects\Location;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\ListingFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property Description $description
 * @property Money $price_per_night Сума в копійках
 * @property Discount|null $discounts
 * @property AcceptGuest|null $accept_guests
 * @property RoomRule|null $rooms_rules
 * @property string $subtype
 * @property array<array-key, mixed>|null $amenities
 * @property AccessibilityFeature|null $accessibility_features
 * @property AvailabilitySetting|null $availability_settings
 * @property Location $location
 * @property HouseRule|null $house_rules
 * @property GuestSafety|null $guest_safety
 * @property string $host_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property PropertyType $type
 * @property string $listing_type
 * @property NoticeType $advance_notice_type
 * @property-read Collection<int, ListingAvailability> $availability
 * @property-read int|null $availability_count
 * @property-read Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read User $host
 * @property-read Collection<int, Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, Reservation> $reservations
 * @property-read int|null $reservations_count
 *
 * @method static ListingFactory factory($count = null, $state = [])
 * @method static Builder<static>|Listing newModelQuery()
 * @method static Builder<static>|Listing newQuery()
 * @method static Builder<static>|Listing query()
 * @method static Builder<static>|Listing whereAcceptGuests($value)
 * @method static Builder<static>|Listing whereAccessibilityFeatures($value)
 * @method static Builder<static>|Listing whereAdvanceNoticeType($value)
 * @method static Builder<static>|Listing whereAmenities($value)
 * @method static Builder<static>|Listing whereAvailabilitySettings($value)
 * @method static Builder<static>|Listing whereCreatedAt($value)
 * @method static Builder<static>|Listing whereDescription($value)
 * @method static Builder<static>|Listing whereDiscounts($value)
 * @method static Builder<static>|Listing whereGuestSafety($value)
 * @method static Builder<static>|Listing whereHostId($value)
 * @method static Builder<static>|Listing whereHouseRules($value)
 * @method static Builder<static>|Listing whereId($value)
 * @method static Builder<static>|Listing whereListingType($value)
 * @method static Builder<static>|Listing whereLocation($value)
 * @method static Builder<static>|Listing wherePricePerNight($value)
 * @method static Builder<static>|Listing whereRoomsRules($value)
 * @method static Builder<static>|Listing whereSlug($value)
 * @method static Builder<static>|Listing whereSubtype($value)
 * @method static Builder<static>|Listing whereTitle($value)
 * @method static Builder<static>|Listing whereType($value)
 * @method static Builder<static>|Listing whereUpdatedAt($value)
 *
 * @property array<array-key, mixed>|null $seo
 * @property-read mixed $meta_image
 *
 * @method static Builder<static>|Listing bySlug(string $slug)
 * @method static Builder<static>|Listing whereSeo($value)
 *
 * @mixin IdeHelperListing
 *
 * @property bool $is_published
 * @property bool $is_featured
 * @property int $views_count
 * @property numeric $rating
 * @property-read int|null $reviews_count
 * @property string|null $deleted_at
 * @property ListingStatus $status
 * @property-read Collection<int, Review> $reviews
 *
 * @method static Builder<static>|Listing applySort(?string $sort)
 * @method static Builder<static>|Listing featured()
 * @method static Builder<static>|Listing filter(array $filters)
 * @method static Builder<static>|Listing filterByAmenities(?array $amenities)
 * @method static Builder<static>|Listing filterByDates(?string $checkIn, ?string $checkOut)
 * @method static Builder<static>|Listing filterByGuests(?int $guests)
 * @method static Builder<static>|Listing filterByLocation(?array $location)
 * @method static Builder<static>|Listing filterByPrice(?float $min = null, ?float $max = null)
 * @method static Builder<static>|Listing filterByRating(?float $minRating)
 * @method static Builder<static>|Listing filterByType(?string $type, ?string $subtype = null)
 * @method static Builder<static>|Listing whereDeletedAt($value)
 * @method static Builder<static>|Listing whereIsFeatured($value)
 * @method static Builder<static>|Listing whereIsPublished($value)
 * @method static Builder<static>|Listing whereRating($value)
 * @method static Builder<static>|Listing whereReviewsCount($value)
 * @method static Builder<static>|Listing whereStatus($value)
 * @method static Builder<static>|Listing whereViewsCount($value)
 * @method static Builder<static>|Listing withAvgRating()
 * @method static Builder<static>|Listing withReviewsCount()
 *
 * @mixin Eloquent
 */
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory, HasSeo, HasUlids;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = self::generateSlug($model->title);
            }

            if (empty($model->seo) && isset($model->description)) {
                $model->seo = self::generateSeoBlock(
                    $model->title,
                    strip_tags($model->description)
                );
            }
        });
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function reservations(): MorphMany
    {
        return $this->morphMany(Reservation::class, 'reservationable');
    }

    public function photos(): MorphMany
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    /**
     * Get all favorites for the listing.
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(ListingAvailability::class, 'listing_id');
    }

    public function scopeFilter($query, array $filters)
    {
        // Базові фільтри
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        // Фільтри за ціною
        if (! empty($filters['price_min'])) {
            $query->where('price_per_night', '>=', $filters['price_min']);
        }
        if (! empty($filters['price_max'])) {
            $query->where('price_per_night', '<=', $filters['price_max']);
        }

        // Фільтри за типом
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Фільтри за рейтингом
        if (! empty($filters['min_rating'])) {
            $query->having('avg_rating', '>=', $filters['min_rating']);
        }

        // Сортування
        if (! empty($filters['sort'])) {
            match ($filters['sort']) {
                'price_asc' => $query->orderBy('price_per_night', 'asc'),
                'price_desc' => $query->orderBy('price_per_night', 'desc'),
                'rating_desc' => $query->orderBy('avg_rating', 'desc'),
                'created_at_desc' => $query->orderBy('created_at', 'desc'),
                default => $query->latest()
            };
        }

        return $query;
    }

    public function scopeFilterByPrice($query, ?float $min = null, ?float $max = null)
    {
        if ($min !== null) {
            $query->where('price_per_night', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price_per_night', '<=', $max);
        }

        return $query;
    }

    public function scopeFilterByType($query, ?string $type, ?string $subtype = null)
    {
        $query = $type ? $query->where('type', $type) : $query;

        if ($type && $subtype) {
            $query->where('type_subtype', $subtype);
        }

        return $query;
    }

    public function scopeFilterByLocation($query, ?array $location)
    {
        if (empty($location)) {
            return $query;
        }

        // Пошук за координатами (геопошук)
        if (! empty($location['coordinates']['latitude']) && ! empty($location['coordinates']['longitude']) && ! empty($location['coordinates']['radius'])) {
            return $query->whereRaw('
                ST_Distance_Sphere(
                    point(longitude, latitude),
                    point(?, ?)
                ) <= ?
            ', [
                $location['coordinates']['longitude'],
                $location['coordinates']['latitude'],
                $location['coordinates']['radius'] * 1000,
            ]);
        }

        // Пошук за полями адреси
        return $query->where(function ($q) use ($location) {
            // Перевіряємо, чи є поле address
            if (! empty($location['address'])) {
                $q->whereRaw("location->>'address' IS NOT NULL");

                // Пошук за країною
                if (! empty($location['address']['country'])) {
                    $q->whereRaw("location->'address'->>'country' ILIKE ?", ["%{$location['address']['country']}%"]);
                }

                // Пошук за містом
                if (! empty($location['address']['city'])) {
                    $q->whereRaw("location->'address'->>'city' ILIKE ?", ["%{$location['address']['city']}%"]);
                }

                // Пошук за вулицею
                if (! empty($location['address']['street'])) {
                    $q->whereRaw("location->'address'->>'street' ILIKE ?", ["%{$location['address']['street']}%"]);
                }

                // Пошук за поштовим індексом
                if (! empty($location['address']['postal_code'])) {
                    $q->whereRaw("location->'address'->>'postal_code' ILIKE ?", ["%{$location['address']['postal_code']}%"]);
                }

                // Пошук за областю/штатом
                if (! empty($location['address']['state'])) {
                    $q->whereRaw("location->'address'->>'state' ILIKE ?", ["%{$location['address']['state']}%"]);
                }
            }
        });
    }

    public function scopeFilterByDates($query, ?string $checkIn, ?string $checkOut)
    {
        if (! $checkIn || ! $checkOut) {
            return $query;
        }

        return $query->whereDoesntHave('reservations', function ($query) use ($checkIn, $checkOut) {
            $query->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })->where('status', '!=', ReservationStatus::CANCELLED_BY_HOST);
        });
    }

    public function scopeFilterByGuests($query, ?int $guests)
    {
        return $guests ? $query->whereRaw("(house_rules->>'number_of_guests')::integer >= ?", [$guests]) : $query;
    }

    public function scopeFilterByAmenities($query, ?array $amenities)
    {
        if (empty($amenities)) {
            return $query;
        }

        return $query->where(function ($q) use ($amenities) {
            foreach ($amenities as $amenity) {
                $q->whereJsonContains('amenities', $amenity);
            }
        });
    }

    /**
     * Filter listings by accessibility features
     */
    public function scopeFilterByAccessibilityFeatures($query, ?array $features)
    {
        if (empty($features)) {
            return $query;
        }

        return $query->where(function ($q) use ($features) {
            foreach ($features as $feature => $value) {
                if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                    $q->whereRaw("(accessibility_features->>'$feature')::boolean = true");
                }
            }
        });
    }

    /**
     * Filter listings by property size
     */
    public function scopeFilterByPropertySize($query, ?float $minSize = null, ?float $maxSize = null)
    {
        if ($minSize !== null) {
            $query->whereRaw("(rooms_rules->>'property_size')::float >= ?", [$minSize]);
        }

        if ($maxSize !== null) {
            $query->whereRaw("(rooms_rules->>'property_size')::float <= ?", [$maxSize]);
        }

        return $query;
    }

    /**
     * Filter listings by year built
     */
    public function scopeFilterByYearBuilt($query, ?int $minYear = null, ?int $maxYear = null)
    {
        if ($minYear !== null) {
            $query->whereRaw("(rooms_rules->>'year_built')::integer >= ?", [$minYear]);
        }

        if ($maxYear !== null) {
            $query->whereRaw("(rooms_rules->>'year_built')::integer <= ?", [$maxYear]);
        }

        return $query;
    }

    /**
     * Filter listings by guest safety features
     */
    public function scopeFilterByGuestSafety($query, ?array $features)
    {
        if (empty($features)) {
            return $query;
        }

        return $query->where(function ($q) use ($features) {
            foreach ($features as $feature => $value) {
                if ($value === true || $value === 'true' || $value === 1 || $value === '1') {
                    $q->whereRaw("(guest_safety->>'$feature')::boolean = true");
                }
            }
        });
    }

    public function scopeFilterByRating($query, ?float $minRating)
    {
        if (! $minRating) {
            return $query;
        }

        return $query->having(
            DB::raw('COALESCE(ROUND(AVG(reviews.overall_rating)::numeric, 1), 0)'),
            '>=',
            $minRating
        );
    }

    public function scopeApplySort($query, ?string $sort)
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price_per_night', 'asc'),
            'price_desc' => $query->orderBy('price_per_night', 'desc'),
            'rating_desc' => $query->orderBy('avg_rating', 'desc'),
            'rating_asc' => $query->orderBy('avg_rating', 'asc'),
            'created_at_desc' => $query->orderBy('created_at', 'desc'),
            'created_at_asc' => $query->orderBy('created_at', 'asc'),
            'title_asc' => $query->orderBy('title', 'asc'),
            'title_desc' => $query->orderBy('title', 'desc'),
            'reviews_count_desc' => $query->orderBy('reviews_count', 'desc'),
            'reviews_count_asc' => $query->orderBy('reviews_count', 'asc'),
            'popularity' => $query->orderBy('views_count', 'desc'),
            default => $query->latest()
        };
    }

    public function scopeWithReviewsCount($query)
    {
        return $query->addSelect([
            'reviews_count' => Review::selectRaw('count(*)')
                ->join('reservations', 'reservations.id', '=', 'reviews.reservation_id')
                ->whereColumn('listings.id', 'reservations.reservationable_id')
                ->where('reservations.reservationable_type', Listing::class),
        ]);
    }

    /**
     * Scope для отримання рекомендованих оголошень
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->where('is_published', true);
    }

    /**
     * Scope для додавання середнього рейтингу оголошення
     */
    public function scopeWithAvgRating($query)
    {
        return $query->select('listings.*')
            ->selectRaw('COALESCE(ROUND(AVG(reviews.overall_rating)::numeric, 1), 0) as avg_rating')
            ->leftJoin('reservations', function ($join) {
                $join->on('listings.id', '=', 'reservations.reservationable_id')
                    ->where('reservations.reservationable_type', Listing::class);
            })
            ->leftJoin('reviews', 'reservations.id', '=', 'reviews.reservation_id')
            ->groupBy('listings.id');
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            Reservation::class,
            'reservationable_id',
            'reservation_id'
        )->where('reservations.reservationable_type', self::class);
    }

    /**
     * Check if the given user is the owner of this listing.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->host_id === $user->id;
    }

    protected function casts(): array
    {
        return [
            'description' => DescriptionCast::class,
            'seo' => 'array',
            'price_per_night' => MoneyIntegerCast::class,
            'discounts' => DiscountCast::class,
            'accept_guests' => AcceptGuestCast::class,
            'rooms_rules' => RoomRuleCast::class,
            'amenities' => 'array',
            'accessibility_features' => AccessibilityFeatureCast::class,
            'availability_settings' => AvailabilitySettingCast::class,
            'location' => LocationCast::class,
            'house_rules' => HouseRuleCast::class,
            'guest_safety' => GuestSafetyCast::class,
            'type' => PropertyType::class,
            'advance_notice_type' => NoticeType::class,
            'status' => ListingStatus::class,
            'listing_type' => ListingType::class,
            'rating' => 'decimal:2',
        ];
    }
}
