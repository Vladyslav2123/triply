<?php

namespace App\Models;

use Database\Factories\ListingAvailabilityFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $listing_id
 * @property \Illuminate\Support\Carbon $date
 * @property bool $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Listing $listing
 *
 * @method static \Database\Factories\ListingAvailabilityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereListingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability whereUpdatedAt($value)
 *
 * @mixin IdeHelperListingAvailability
 *
 * @property int|null $price_override Сума в копійках
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ListingAvailability wherePriceOverride($value)
 *
 * @mixin \Eloquent
 */
class ListingAvailability extends Model
{
    /** @use HasFactory<ListingAvailabilityFactory> */
    use HasFactory, HasUlids;

    protected $hidden = ['created_at', 'updated_at'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d',
            'is_available' => 'boolean',
        ];
    }
}
