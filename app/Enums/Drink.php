<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Drink: string
{
    use EnumHelper;

    case APERITIF = 'aperitif';
    case BEER = 'beer';
    case COCKTAIL = 'cocktail';
    case COFFEE = 'coffee';
    case JUICE = 'juice';
    case NON_ALCOHOLIC = 'non_alcoholic';
    case ALCOHOLIC = 'alcoholic';
    case TEA = 'tea';
    case WATER = 'water';
    case WINE = 'wine';
    case OTHER = 'other';
}
