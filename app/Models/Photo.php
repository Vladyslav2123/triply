<?php

namespace App\Models;

use Database\Factories\PhotoFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $url
 * @property string $disk
 * @property string|null $directory
 * @property int|null $size
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $width
 * @property int|null $height
 * @property string $photoable_type
 * @property string $photoable_id
 * @property Carbon $uploaded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Model|Eloquent $photoable
 * @property-read string $full_url
 *
 * @method static PhotoFactory factory($count = null, $state = [])
 * @method static Builder<static>|Photo newModelQuery()
 * @method static Builder<static>|Photo newQuery()
 * @method static Builder<static>|Photo query()
 * @method static Builder<static>|Photo whereCreatedAt($value)
 * @method static Builder<static>|Photo whereId($value)
 * @method static Builder<static>|Photo wherePhotoableId($value)
 * @method static Builder<static>|Photo wherePhotoableType($value)
 * @method static Builder<static>|Photo whereUpdatedAt($value)
 * @method static Builder<static>|Photo whereUploadedAt($value)
 * @method static Builder<static>|Photo whereUrl($value)
 *
 * @mixin IdeHelperPhoto
 *
 * @method static Builder<static>|Photo onlyTrashed()
 * @method static Builder<static>|Photo whereDeletedAt($value)
 * @method static Builder<static>|Photo whereDirectory($value)
 * @method static Builder<static>|Photo whereDisk($value)
 * @method static Builder<static>|Photo whereHeight($value)
 * @method static Builder<static>|Photo whereMimeType($value)
 * @method static Builder<static>|Photo whereOriginalFilename($value)
 * @method static Builder<static>|Photo whereSize($value)
 * @method static Builder<static>|Photo whereWidth($value)
 * @method static Builder<static>|Photo withTrashed()
 * @method static Builder<static>|Photo withoutTrashed()
 *
 * @mixin Eloquent
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = ['full_url'];

    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getFullUrlAttribute(): string
    {
        $disk = config('filesystems.default', 's3');

        return Storage::disk($disk)->url($this->url);
    }

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'url' => 'string',
            'disk' => 'string',
            'directory' => 'string',
            'size' => 'integer',
            'original_filename' => 'string',
            'mime_type' => 'string',
            'width' => 'integer',
            'height' => 'integer',
            'uploaded_at' => 'datetime:Y-m-d',
            'deleted_at' => 'datetime',
        ];
    }
}
