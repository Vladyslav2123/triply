<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Food: string
{
    use EnumHelper;

    case SNACK = 'snack';
    case APPETIZER = 'appetizer';
    case BREAKFAST = 'breakfast';
    case LUNCH = 'lunch';
    case DINNER = 'dinner';
    case DESSERT = 'dessert';
    case TASTING_MENU = 'tasting_menu';
    case OTHER = 'other';
}
