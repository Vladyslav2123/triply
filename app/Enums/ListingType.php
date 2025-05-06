<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum ListingType: string
{
    use EnumHelper;

    case ENTIRE_PLACE = 'entire_place';
    case ROOM = 'room';
    case SHARED_ROOM = 'shared_room';
}
