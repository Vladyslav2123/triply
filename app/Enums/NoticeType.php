<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum NoticeType: string
{
    use EnumHelper;

    case SAME_DAY = 'same_day';
    case IN_DAYS = 'in_days';
}
