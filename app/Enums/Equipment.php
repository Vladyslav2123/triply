<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Equipment: string
{
    use EnumHelper;

    case SPORTS_EQUIPMENT = 'sports_equipment';
    case TOURIST_GEAR = 'tourist_gear';
    case PROTECTIVE_EQUIPMENT = 'protective_equipment';
    case CREATIVE_MATERIALS = 'creative_materials';
    case CAMERA = 'camera';
    case PHOTOGRAPHY = 'photography';
    case OTHER = 'other';
}
