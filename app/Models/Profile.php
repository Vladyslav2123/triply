<?php

namespace App\Models;

use App\Casts\LocationCast;
use App\Enums\EducationLevel;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use App\Models\Traits\Attributes\ProfileAttributes;
use App\Models\Traits\Concerns\HasPhoto;
use App\Models\Traits\Methods\ProfileMethods;
use App\Models\Traits\Relationships\ProfileRelationships;
use Database\Factories\ProfileFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $phone
 * @property Carbon|null $birth_date
 * @property Gender|null $gender
 * @property string|null $country
 * @property string|null $city
 * @property string|null $address
 * @property string|null $postal_code
 * @property string|null $work
 * @property string|null $job_title
 * @property string|null $company
 * @property string|null $school
 * @property EducationLevel|null $education_level
 * @property string|null $dream_destination
 * @property array<array-key, mixed>|null $next_destinations
 * @property bool $travel_history
 * @property string|null $favorite_travel_type
 * @property string|null $time_spent_on
 * @property string|null $useless_skill
 * @property string|null $pets
 * @property bool $birth_decade
 * @property string|null $favorite_high_school_song
 * @property string|null $fun_fact
 * @property string|null $obsession
 * @property string|null $biography_title
 * @property Collection<int, Language>|null $languages
 * @property string|null $location
 * @property string|null $about
 * @property Collection<int, Interest>|null $interests
 * @property string|null $facebook_url
 * @property string|null $instagram_url
 * @property string|null $twitter_url
 * @property string|null $linkedin_url
 * @property bool $email_notifications
 * @property bool $sms_notifications
 * @property string $preferred_language
 * @property string $preferred_currency
 * @property bool $is_verified
 * @property Carbon|null $verified_at
 * @property string|null $verification_method
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read string $full_name
 * @property-read string $address_formatted
 * @property-read array<string, string|null> $social_links
 * @property-read int|null $age
 * @property-read Photo|null $photo
 *
 * @method static ProfileFactory factory($count = null, $state = [])
 * @method static Builder<static>|Profile newModelQuery()
 * @method static Builder<static>|Profile newQuery()
 * @method static Builder<static>|Profile query()
 * @method static Builder<static>|Profile whereAbout($value)
 * @method static Builder<static>|Profile whereBiographyTitle($value)
 * @method static Builder<static>|Profile whereBirthDecade($value)
 * @method static Builder<static>|Profile whereCreatedAt($value)
 * @method static Builder<static>|Profile whereDreamDestination($value)
 * @method static Builder<static>|Profile whereFavoriteHighSchoolSong($value)
 * @method static Builder<static>|Profile whereFunFact($value)
 * @method static Builder<static>|Profile whereId($value)
 * @method static Builder<static>|Profile whereInterests($value)
 * @method static Builder<static>|Profile whereLanguages($value)
 * @method static Builder<static>|Profile whereLocation($value)
 * @method static Builder<static>|Profile whereNextDestinations($value)
 * @method static Builder<static>|Profile whereObsession($value)
 * @method static Builder<static>|Profile wherePets($value)
 * @method static Builder<static>|Profile whereSchool($value)
 * @method static Builder<static>|Profile whereTimeSpentOn($value)
 * @method static Builder<static>|Profile whereTravelHistory($value)
 * @method static Builder<static>|Profile whereUpdatedAt($value)
 * @method static Builder<static>|Profile whereUselessSkill($value)
 * @method static Builder<static>|Profile whereUserId($value)
 * @method static Builder<static>|Profile whereWork($value)
 *
 * @mixin IdeHelperProfile
 *
 * @property bool $is_superhost
 * @property float $response_speed
 * @property int $views_count
 * @property float $rating
 * @property int $reviews_count
 * @property Carbon|null $last_active_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder<static>|Profile onlyTrashed()
 * @method static Builder<static>|Profile whereBirthDate($value)
 * @method static Builder<static>|Profile whereCompany($value)
 * @method static Builder<static>|Profile whereDeletedAt($value)
 * @method static Builder<static>|Profile whereEducationLevel($value)
 * @method static Builder<static>|Profile whereEmailNotifications($value)
 * @method static Builder<static>|Profile whereFacebookUrl($value)
 * @method static Builder<static>|Profile whereFavoriteTravelType($value)
 * @method static Builder<static>|Profile whereFirstName($value)
 * @method static Builder<static>|Profile whereGender($value)
 * @method static Builder<static>|Profile whereInstagramUrl($value)
 * @method static Builder<static>|Profile whereIsSuperhost($value)
 * @method static Builder<static>|Profile whereIsVerified($value)
 * @method static Builder<static>|Profile whereJobTitle($value)
 * @method static Builder<static>|Profile whereLastActiveAt($value)
 * @method static Builder<static>|Profile whereLastName($value)
 * @method static Builder<static>|Profile whereLinkedinUrl($value)
 * @method static Builder<static>|Profile wherePreferredCurrency($value)
 * @method static Builder<static>|Profile wherePreferredLanguage($value)
 * @method static Builder<static>|Profile whereRating($value)
 * @method static Builder<static>|Profile whereResponseSpeed($value)
 * @method static Builder<static>|Profile whereReviewsCount($value)
 * @method static Builder<static>|Profile whereSmsNotifications($value)
 * @method static Builder<static>|Profile whereTwitterUrl($value)
 * @method static Builder<static>|Profile whereVerificationMethod($value)
 * @method static Builder<static>|Profile whereVerifiedAt($value)
 * @method static Builder<static>|Profile whereViewsCount($value)
 * @method static Builder<static>|Profile withTrashed()
 * @method static Builder<static>|Profile withoutTrashed()
 *
 * @mixin Eloquent
 */
class Profile extends Model
{
    /** @use HasFactory<ProfileFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    use HasPhoto, ProfileAttributes, ProfileMethods, ProfileRelationships;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_superhost' => 'boolean',
            'response_speed' => 'float',
            'views_count' => 'integer',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'education_level' => EducationLevel::class,
            'travel_history' => 'boolean',
            'next_destinations' => 'array',
            'gender' => Gender::class,
            'birth_decade' => 'boolean',
            'languages' => AsEnumCollection::of(Language::class),
            'interests' => AsEnumCollection::of(Interest::class),
            'location' => LocationCast::class,
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
