<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum SkillLevel: string
{
    use EnumHelper;

    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';
    case EXPERT = 'expert';
}
