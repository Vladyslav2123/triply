<?php

namespace App\Models;

use Database\Factories\MessageFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $sender_id
 * @property string $recipient_id
 * @property string $content
 * @property Carbon $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $recipient
 * @property-read User $sender
 *
 * @method static MessageFactory factory($count = null, $state = [])
 * @method static Builder<static>|Message newModelQuery()
 * @method static Builder<static>|Message newQuery()
 * @method static Builder<static>|Message query()
 * @method static Builder<static>|Message whereContent($value)
 * @method static Builder<static>|Message whereCreatedAt($value)
 * @method static Builder<static>|Message whereId($value)
 * @method static Builder<static>|Message whereRecipientId($value)
 * @method static Builder<static>|Message whereSenderId($value)
 * @method static Builder<static>|Message whereSentAt($value)
 * @method static Builder<static>|Message whereUpdatedAt($value)
 *
 * @mixin IdeHelperMessage
 *
 * @property string|null $read_at
 * @property string|null $deleted_at
 *
 * @method static Builder<static>|Message whereDeletedAt($value)
 * @method static Builder<static>|Message whereReadAt($value)
 *
 * @mixin Eloquent
 */
class Message extends Model
{
    /** @use HasFactory<MessageFactory> */
    use HasFactory, HasUlids;

    /**
     * @var list<string>
     */
    protected $hidden = ['created_at', 'updated_at'];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime:Y-m-d H:i',
        ];
    }
}
