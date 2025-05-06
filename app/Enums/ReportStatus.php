<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ReportStatus: string
{
    use EnumHelper;

    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'В очікуванні',
            self::RESOLVED => 'Вирішено',
            self::REJECTED => 'Відхилено',
        };
    }
}
