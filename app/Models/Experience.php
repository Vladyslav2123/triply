<?php

namespace App\Models;

use App\Casts\BookingRulesCast;
use App\Casts\CancellationPolicyCast;
use App\Casts\DiscountCast;
use App\Casts\ExperienceTypeCast;
use App\Casts\GroupSizeCast;
use App\Casts\GuestNeedsCast;
use App\Casts\GuestRequirementsCast;
use App\Casts\HostBioCast;
use App\Casts\HostLicensesCast;
use App\Casts\HostProvidesCast;
use App\Casts\HostVerificationCast;
use App\Casts\LocationCast;
use App\Casts\LocationTypeCast;
use App\Casts\PricingCast;
use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Enums\LocationType;
use App\Models\Traits\Concerns\HasSeo;
use App\ValueObjects\Experience\BookingRules;
use App\ValueObjects\Experience\CancellationPolicy;
use App\ValueObjects\Experience\GroupSize;
use App\ValueObjects\Experience\GuestNeeds;
use App\ValueObjects\Experience\GuestRequirements;
use App\ValueObjects\Experience\HostBio;
use App\ValueObjects\Experience\HostProvides;
use App\ValueObjects\Experience\Pricing;
use App\ValueObjects\Listing\Discount;
use App\ValueObjects\Location;
use Database\Factories\ExperienceFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $host_id
 * @property string $slug
 * @property array<array-key, mixed>|null $seo
 * @property Location $location
 * @property string $title
 * @property array<array-key, mixed> $languages
 * @property string $sub_category
 * @property string $reviews
 * @property string $description
 * @property Carbon $duration
 * @property string|null $location_note
 * @property string $location_subtype
 * @property HostBio $host_bio
 * @property Location $address
 * @property HostProvides|null $host_provides
 * @property GuestNeeds|null $guest_needs
 * @property GuestRequirements $guest_requirements
 * @property string $name
 * @property GroupSize $grouping
 * @property Carbon $starts_at
 * @property Pricing $pricing
 * @property string $discounts
 * @property BookingRules $booking_rules
 * @property CancellationPolicy $cancellation_policy
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ExperienceType $category
 * @property LocationType $location_type
 * @property ExperienceStatus $status
 * @property Discount $discount
 * @property-read Collection<int, ExperienceAvailability> $availability
 * @property-read int|null $availability_count
 * @property-read Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read User $host
 * @property-read mixed $meta_image
 * @property-read Collection<int, Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, Reservation> $reservations
 * @property-read int|null $reservations_count
 *
 * @method static Builder<static>|Experience bySlug(string $slug)
 * @method static ExperienceFactory factory($count = null, $state = [])
 * @method static Builder<static>|Experience newModelQuery()
 * @method static Builder<static>|Experience newQuery()
 * @method static Builder<static>|Experience query()
 * @method static Builder<static>|Experience whereAddress($value)
 * @method static Builder<static>|Experience whereBookingRules($value)
 * @method static Builder<static>|Experience whereCancellationPolicy($value)
 * @method static Builder<static>|Experience whereCategory($value)
 * @method static Builder<static>|Experience whereCreatedAt($value)
 * @method static Builder<static>|Experience whereDescription($value)
 * @method static Builder<static>|Experience whereDiscounts($value)
 * @method static Builder<static>|Experience whereDuration($value)
 * @method static Builder<static>|Experience whereGrouping($value)
 * @method static Builder<static>|Experience whereGuestNeeds($value)
 * @method static Builder<static>|Experience whereGuestRequirements($value)
 * @method static Builder<static>|Experience whereHostBio($value)
 * @method static Builder<static>|Experience whereHostId($value)
 * @method static Builder<static>|Experience whereHostProvides($value)
 * @method static Builder<static>|Experience whereId($value)
 * @method static Builder<static>|Experience whereLanguages($value)
 * @method static Builder<static>|Experience whereLocation($value)
 * @method static Builder<static>|Experience whereLocationNote($value)
 * @method static Builder<static>|Experience whereLocationSubtype($value)
 * @method static Builder<static>|Experience whereLocationType($value)
 * @method static Builder<static>|Experience whereName($value)
 * @method static Builder<static>|Experience wherePricing($value)
 * @method static Builder<static>|Experience whereReviews($value)
 * @method static Builder<static>|Experience whereSeo($value)
 * @method static Builder<static>|Experience whereSlug($value)
 * @method static Builder<static>|Experience whereStartsAt($value)
 * @method static Builder<static>|Experience whereStatus($value)
 * @method static Builder<static>|Experience whereSubCategory($value)
 * @method static Builder<static>|Experience whereTitle($value)
 * @method static Builder<static>|Experience whereUpdatedAt($value)
 *
 * @mixin IdeHelperExperience
 *
 * @property bool $is_published
 * @property bool $is_featured
 * @property int $views_count
 * @property float $rating
 * @property int $reviews_count
 * @property \App\ValueObjects\Experience\HostVerification|null $host_verification
 * @property \App\ValueObjects\Experience\HostLicenses|null $host_licenses
 * @property string|null $deleted_at
 *
 * @method static Builder<static>|Experience whereDeletedAt($value)
 * @method static Builder<static>|Experience whereHostLicenses($value)
 * @method static Builder<static>|Experience whereHostVerification($value)
 * @method static Builder<static>|Experience whereIsFeatured($value)
 * @method static Builder<static>|Experience whereIsPublished($value)
 * @method static Builder<static>|Experience whereRating($value)
 * @method static Builder<static>|Experience whereReviewsCount($value)
 * @method static Builder<static>|Experience whereViewsCount($value)
 *
 * @mixin Eloquent
 */
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory, HasSeo, HasUlids;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $attributes = [
        'host_licenses' => '{}',
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

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function availability(): HasMany
    {
        return $this->hasMany(ExperienceAvailability::class, 'experience_id');
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
            'seo' => 'array',
            'location' => LocationCast::class,
            'languages' => AsEnumCollection::of(Language::class),
            'duration' => 'datetime:d-m-Y',
            'host_bio' => HostBioCast::class,
            'address' => LocationCast::class,
            'host_provides' => HostProvidesCast::class,
            'guest_needs' => GuestNeedsCast::class,
            'guest_requirements' => GuestRequirementsCast::class,
            'grouping' => GroupSizeCast::class,
            'starts_at' => 'datetime:d-m-Y',
            'pricing' => PricingCast::class,
            'discount' => DiscountCast::class,
            'booking_rules' => BookingRulesCast::class,
            'cancellation_policy' => CancellationPolicyCast::class,
            'category' => ExperienceTypeCast::class,
            'location_type' => LocationTypeCast::class,
            'status' => ExperienceStatus::class,
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'host_verification' => HostVerificationCast::class,
            'host_licenses' => HostLicensesCast::class,
        ];
    }
}
