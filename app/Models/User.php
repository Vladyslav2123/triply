<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Traits\Attributes\UserAttributes;
use App\Models\Traits\Concerns\HasFavorites;
use App\Models\Traits\Concerns\HasProfile;
use App\Models\Traits\Concerns\HasRoles;
use App\Models\Traits\Concerns\HasSlug;
use App\Models\Traits\Methods\HasMethods;
use App\Models\Traits\QueryBuilders\HasQueryBuilders;
use App\Models\Traits\Relationships\UserRelationships;
use Database\Factories\UserFactory;
use Eloquent;
use Filament\Models\Contracts\HasName;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $id
 * @property UserRole $role
 * @property string $slug
 * @property string|null $phone
 * @property Carbon|null $birth_date
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property-read Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read Collection<int, Listing> $listings
 * @property-read int|null $listings_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Photo|null $photo
 * @property-read Profile|null $profile
 * @property-read Collection<int, Message> $receivedMessages
 * @property-read int|null $received_messages_count
 * @property-read Collection<int, Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, Message> $sentMessages
 * @property-read int|null $sent_messages_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereBirthDate($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePhone($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereRole($value)
 * @method static Builder<static>|User whereSlug($value)
 * @method static Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder<static>|User whereTwoFactorSecret($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 *
 * @property-read Collection<int, Experience> $experiences
 * @property-read int|null $experiences_count
 * @property-read string $full_name
 * @property-read string $url
 *
 * @mixin IdeHelperUser
 *
 * @property bool $is_banned
 * @property-read float $average_rating
 * @property-read bool $is_verified
 * @property-read string $joined_date
 * @property-read int $unread_messages_count
 *
 * @method static Builder<static>|User whereIsBanned($value)
 *
 * @mixin Eloquent
 */
class User extends Authenticatable implements HasName, MustVerifyEmail
{
    /** @use HasFactory<UserFactory>
     */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    use HasFavorites, HasMethods, HasProfile, HasQueryBuilders, HasRoles, HasSlug, UserAttributes, UserRelationships;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (! isset($user->role)) {
                $user->role = UserRole::USER;
            }

            $user->generateSlug();
        });
    }

    /**
     * Get the name of the user for Filament.
     */
    public function getFilamentName(): string
    {
        return $this->full_name;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_banned' => 'boolean',
            'role' => UserRole::class,
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
        ];
    }
}
