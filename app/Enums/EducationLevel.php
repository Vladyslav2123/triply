<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum EducationLevel: string
{
    use EnumHelper;

    case HIGH_SCHOOL = 'high_school';
    case BACHELOR = 'bachelor';
    case MASTER = 'master';
    case PHD = 'phd';
    case OTHER = 'other';
}
