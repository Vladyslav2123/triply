<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ExperienceStatus: string
{
    use EnumHelper;

    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';
    case ARCHIVED = 'archived';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
