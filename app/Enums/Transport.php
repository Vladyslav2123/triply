<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Transport: string
{
    use EnumHelper;

    case BUS = 'bus';
    case TRAIN = 'train';
    case CAR = 'car';
    case BICYCLE = 'bicycle';
    case BOAT = 'boat';
    case CRUISE = 'cruise';
    case FERRY = 'ferry';
    case HELICOPTER = 'helicopter';
    case MOTORCYCLE = 'motorcycle';
    case SUV = 'suv';
    case SCOOTER = 'scooter';
}
