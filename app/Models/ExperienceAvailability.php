<?php

namespace App\Models;

use Database\Factories\ExperienceAvailabilityFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $experience_id
 * @property \Illuminate\Support\Carbon $date
 * @property bool $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Experience $experience
 *
 * @method static \Database\Factories\ExperienceAvailabilityFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereExperienceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereUpdatedAt($value)
 *
 * @mixin IdeHelperExperienceAvailability
 *
 * @property int|null $spots_available Available spots for this time slot
 * @property int|null $price_override Сума в копійках
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability wherePriceOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExperienceAvailability whereSpotsAvailable($value)
 *
 * @mixin \Eloquent
 */
class ExperienceAvailability extends Model
{
    /** @use HasFactory<ExperienceAvailabilityFactory> */
    use HasFactory, HasUlids;

    protected $hidden = ['created_at', 'updated_at'];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class, 'experience_id');
    }

    protected function casts(): array
    {
        return [
            'date' => 'datetime:Y-m-d',
            'is_available' => 'boolean',
        ];
    }
}
