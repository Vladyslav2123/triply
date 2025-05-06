<?php

namespace App\Models;

use Database\Factories\FavoriteFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $user_id
 * @property Carbon $added_at
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read User $user
 *
 * @method static FavoriteFactory factory($count = null, $state = [])
 * @method static Builder<static>|Favorite newModelQuery()
 * @method static Builder<static>|Favorite newQuery()
 * @method static Builder<static>|Favorite query()
 * @method static Builder<static>|Favorite whereAddedAt($value)
 * @method static Builder<static>|Favorite whereCreatedAt($value)
 * @method static Builder<static>|Favorite whereId($value)
 * @method static Builder<static>|Favorite whereListingId($value)
 * @method static Builder<static>|Favorite whereUpdatedAt($value)
 * @method static Builder<static>|Favorite whereUserId($value)
 *
 * @property string $favoriteable_type
 * @property string $favoriteable_id
 * @property-read Model|Eloquent $favoriteable
 *
 * @method static Builder<static>|Favorite whereFavoriteableId($value)
 * @method static Builder<static>|Favorite whereFavoriteableType($value)
 *
 * @mixin IdeHelperFavorite
 * @mixin Eloquent
 */
class Favorite extends Model
{
    /** @use HasFactory<FavoriteFactory> */
    use HasFactory, HasUlids;

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'added_at' => 'datetime:Y-m-d',
        ];
    }
}
