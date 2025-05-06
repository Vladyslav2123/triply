<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;
use App\Enums\Traits\HasSubtypes;

enum LocationType: string
{
    use EnumHelper;
    use HasSubtypes;

    case RELIGIOUS_CULTURAL = 'religious_and_cultural_site';
    case TOURIST_ATTRACTION = 'tourist_attraction';
    case GOVERNMENT_EDUCATIONAL = 'government_or_educational_institution';
    case SPORTS_WELLNESS = 'sports_and_wellness_center';
    case ENTERTAINMENT_VENUE = 'entertainment_venue';
    case NATURE_OUTDOORS = 'nature_and_outdoors';
    case COMMERCIAL_AREA = 'commercial_area';
    case HISTORICAL_SITE = 'historical_site';

    private const SUBTYPES = [
        self::RELIGIOUS_CULTURAL->value => [
            'temple',
            'church',
            'mosque',
            'synagogue',
            'cultural_center',
            'shrine',
        ],
        self::TOURIST_ATTRACTION->value => [
            'amusement_park',
            'zoo',
            'aquarium',
            'famous_landmark',
            'panoramic_viewpoint',
        ],
        self::GOVERNMENT_EDUCATIONAL->value => [
            'university',
            'school',
            'city_hall',
            'embassy',
            'library',
            'museum',
        ],
        self::SPORTS_WELLNESS->value => [
            'gym',
            'spa',
            'yoga_studio',
            'stadium',
            'swimming_pool',
            'martial_arts_center',
        ],
        self::ENTERTAINMENT_VENUE->value => [
            'theater',
            'concert_hall',
            'nightclub',
            'cinema',
            'arcade',
        ],
        self::NATURE_OUTDOORS->value => [
            'national_park',
            'beach',
            'lake',
            'forest',
            'hiking_trail',
            'botanical_garden',
        ],
        self::COMMERCIAL_AREA->value => [
            'shopping_mall',
            'market',
            'business_district',
            'coffee_shop',
            'coworking_space',
        ],
        self::HISTORICAL_SITE->value => [
            'castle',
            'fortress',
            'ancient_ruins',
            'monument',
            'historical_building',
        ],
    ];

    protected static function getTypeKey(): string
    {
        return 'location_type';
    }

    protected static function getSubtypeKey(): string
    {
        return 'location_subtype';
    }

    public function getSubtypes(): array
    {
        return self::SUBTYPES[$this->value];
    }
}
