<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;
use App\Enums\Traits\HasSubtypes;

enum ExperienceType: string
{
    use EnumHelper;
    use HasSubtypes;

    case ART_DESIGN = 'art_and_design';
    case FOOD_DRINK = 'food_and_drink';
    case NATURE = 'nature_and_outdoors';
    case SPORTS = 'sports_and_activities';
    case WELLNESS = 'wellness';
    case MUSIC = 'music';
    case HISTORY = 'history_and_culture';
    case NIGHTLIFE = 'nightlife';
    case WORKSHOP = 'workshops';
    case ANIMALS = 'animals';
    case PHOTOGRAPHY = 'photography';
    case LOCAL_LIFE = 'local_life';

    private const SUBTYPES = [
        self::ART_DESIGN->value => [
            'fashion_class',
            'photography_class',
            'art_class',
            'art_exhibition',
            'street_art_tour',
        ],
        self::FOOD_DRINK->value => [
            'wine_tasting',
            'cooking_class',
            'bar_tour',
            'food_tour',
        ],
        self::NATURE->value => [
            'hiking',
            'nature_walk',
            'biking_tour',
            'camping',
            'fishing',
        ],
        self::SPORTS->value => [
            'beach_yoga',
            'surfing',
            'kayaking',
            'golf',
            'walking_tour',
        ],
        self::WELLNESS->value => [
            'meditation',
            'spa_treatments',
            'fitness_session',
            'yoga',
        ],
        self::MUSIC->value => [
            'live_music',
            'concert',
            'dj_session',
            'instrument_lesson',
        ],
        self::HISTORY->value => [
            'museum_tour',
            'historic_district_walk',
            'old_town_tour',
        ],
        self::NIGHTLIFE->value => [
            'nightclub_tour',
            'bar_hopping',
            'cocktail_party',
        ],
        self::WORKSHOP->value => [
            'pottery_workshop',
            'painting_class',
            'handicrafts',
            'floral_design',
        ],
        self::ANIMALS->value => [
            'petting_zoo',
            'horseback_riding',
            'birdwatching',
            'animal_care_experience',
        ],
        self::PHOTOGRAPHY->value => [
            'city_photo_walk',
            'photography_lesson',
            'portrait_session',
            'nature_photography',
        ],
        self::LOCAL_LIFE->value => [
            'walk_with_local',
            'local_market_shopping',
            'cultural_exchange',
            'home_dinner',
        ],
    ];

    protected static function getTypeKey(): string
    {
        return 'experience_type';
    }

    protected static function getSubtypeKey(): string
    {
        return 'experience_subtype';
    }

    public function getSubtypes(): array
    {
        return self::SUBTYPES[$this->value];
    }
}
