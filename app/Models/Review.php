<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $reservation_id
 * @property string $reviewer_id
 * @property float $overall_rating 1-5
 * @property int $cleanliness_rating 1-5
 * @property int $accuracy_rating 1-5
 * @property int $checkin_rating 1-5
 * @property int $communication_rating 1-5
 * @property int $location_rating 1-5
 * @property int $value_rating 1-5
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Reservation $reservation
 * @property-read User $reviewer
 *
 * @method static ReviewFactory factory($count = null, $state = [])
 * @method static Builder<static>|Review newModelQuery()
 * @method static Builder<static>|Review newQuery()
 * @method static Builder<static>|Review query()
 *
 * @mixin IdeHelperReview
 *
 * @property string|null $deleted_at
 * @property-read string|null $reservation_title
 *
 * @method static Builder<static>|Review byUser(\App\Models\User $user)
 * @method static Builder<static>|Review forReservationable(\Illuminate\Database\Eloquent\Model $reservationable)
 * @method static Builder<static>|Review whereAccuracyRating($value)
 * @method static Builder<static>|Review whereCheckinRating($value)
 * @method static Builder<static>|Review whereCleanlinessRating($value)
 * @method static Builder<static>|Review whereComment($value)
 * @method static Builder<static>|Review whereCommunicationRating($value)
 * @method static Builder<static>|Review whereCreatedAt($value)
 * @method static Builder<static>|Review whereDeletedAt($value)
 * @method static Builder<static>|Review whereId($value)
 * @method static Builder<static>|Review whereLocationRating($value)
 * @method static Builder<static>|Review whereOverallRating($value)
 * @method static Builder<static>|Review whereReservationId($value)
 * @method static Builder<static>|Review whereReviewerId($value)
 * @method static Builder<static>|Review whereUpdatedAt($value)
 * @method static Builder<static>|Review whereValueRating($value)
 *
 * @mixin \Eloquent
 */
class Review extends Model
{
    use HasFactory, HasUlids;

    /**
     * Disable mass assignment protection
     */
    protected static function boot()
    {
        parent::boot();
        static::unguard();
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'overall_rating' => 'float',
        'cleanliness_rating' => 'integer',
        'accuracy_rating' => 'integer',
        'checkin_rating' => 'integer',
        'communication_rating' => 'integer',
        'location_rating' => 'integer',
        'value_rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the reservation associated with the review.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Get the user who wrote the review.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the title of the reservationable entity.
     */
    public function getReservationTitleAttribute(): ?string
    {
        return $this->reservation?->reservationable?->title ?? null;
    }

    /**
     * Scope a query to only include reviews for a specific reservationable entity.
     */
    public function scopeForReservationable(Builder $query, Model $reservationable): Builder
    {
        return $query->whereHas('reservation', function ($query) use ($reservationable) {
            $query->where('reservationable_id', $reservationable->id)
                ->where(function ($q) use ($reservationable) {
                    $q->where('reservationable_type', get_class($reservationable))
                        ->orWhere('reservationable_type', strtolower(class_basename($reservationable)));
                });
        });
    }

    /**
     * Scope a query to only include reviews by a specific user.
     */
    public function scopeByUser(Builder $query, User $user): Builder
    {
        return $query->where('reviewer_id', $user->id);
    }

    /**
     * Check if the review belongs to the given user.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->reviewer_id === $user->id;
    }
}
