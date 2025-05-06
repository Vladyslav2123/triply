<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Gender: string
{
    use EnumHelper;

    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';
    case PREFER_NOT_TO_SAY = 'prefer_not_to_say';
}
