<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum PhysicalActivityLevel: string
{
    use EnumHelper;

    case NONE = 'none';
    case LOW = 'low';
    case MODERATE = 'moderate';
    case HIGH = 'high';
    case INTENSE = 'intense';
}
