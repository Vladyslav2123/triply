<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;
use App\Enums\Traits\HasSubtypes;

enum PropertyType: string
{
    use EnumHelper;
    use HasSubtypes;

    case APARTMENT = 'apartment';
    case HOUSE = 'house';
    case SECONDARY_UNIT = 'secondary_unit';
    case BED_AND_BREAKFAST = 'bed_and_breakfast';
    case BOUTIQUE_HOTEL = 'boutique_hotel';

    private const SUBTYPES = [
        self::APARTMENT->value => ['rental_unit', 'condo', 'serviced_apartment', 'loft'],
        self::HOUSE->value => ['home', 'bungalow', 'townhouse', 'cabin', 'chalet', 'earthen_home', 'hut', 'lighthouse', 'villa', 'dome', 'cottage', 'farm_stay', 'houseboat', 'tiny_home'],
        self::SECONDARY_UNIT->value => ['guest_suite', 'guest_house', 'farm_stay'],
        self::BED_AND_BREAKFAST->value => ['bed and breakfast', 'nature_lodge', 'farm_stay'],
        self::BOUTIQUE_HOTEL->value => ['boutique_hotel', 'nature_lodge', 'hostel', 'serviced_apartment', 'aparthotel', 'hotel', 'resort'],
    ];

    public function getSubtypes(): array
    {
        return self::SUBTYPES[$this->value];
    }
}
