<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;
use App\Enums\Traits\HasSubtypes;

enum Amenity: string
{
    use EnumHelper;
    use HasSubtypes;

    case BASICS = 'basics';
    case BATHROOM = 'bathroom';
    case BEDROOM_LAUNDRY = 'bedroom_and_laundry';
    case ENTERTAINMENT = 'entertainment';
    case FAMILY = 'family';
    case HEATING_COOLING = 'heating_and_cooling';
    case HOME_SAFETY = 'home_safety';
    case INTERNET_OFFICE = 'internet_and_office';
    case KITCHEN_DINING = 'kitchen_and_dining';
    case LOCATION_FEATURES = 'location_features';
    case OUTDOOR = 'outdoor';
    case PARKING_FACILITIES = 'parking_and_facilities';
    case SERVICES = 'services';

    private const SUBTYPES = [
        self::BASICS->value => [
            'air_conditioning',
            'dryer',
            'essentials',
            'heating',
            'hot_water',
            'kitchen',
            'tv',
            'washer',
            'wifi',
        ],
        self::BATHROOM->value => [
            'bathtub',
            'bidet',
            'body_soap',
            'cleaning_products',
            'conditioner',
            'hair_dryer',
            'hot_water',
            'outdoor_shower',
            'shampoo',
            'shower_gel',
        ],
        self::BEDROOM_LAUNDRY->value => [
            'bed_linens',
            'clothing_storage',
            'dryer',
            'drying_rack_for_clothing',
            'essentials',
            'extra_pillows_and_blankets',
            'hangers',
            'iron',
            'mosquito_net',
            'room_darkening_shades',
            'washer',
        ],
        self::ENTERTAINMENT->value => [
            'arcade_games',
            'batting_cage',
            'books_and_reading_material',
            'bowling_alley',
            'climbing_wall',
            'ethernet_connection',
            'exercise_equipment',
            'game_console',
            'laser_tag',
            'life_size_games',
            'mini_golf',
            'movie_theater',
            'piano',
            'ping_pong_table',
            'pool_table',
            'record_player',
            'skate_ramp',
            'sound_system',
            'theme_room',
            'tv',
        ],
        self::FAMILY->value => [
            'baby_bath',
            'baby_monitor',
            'baby_safety_gates',
            'babysitter_recommendations',
            'board_games',
            'changing_table',
            'childrens_playroom',
            'childrens_bikes',
            'childrens_books_and_toys',
            'childrens_dinnerware',
            'crib',
            'fire_screen',
            'high_chair',
            'outdoor_playground',
            'outlet_covers',
            'pack_n_play_travel_crib',
            'table_corner_guards',
            'window_guards',
        ],
        self::HEATING_COOLING->value => [
            'ceiling_fan',
            'portable_fans',
            'air_conditioning',
            'heating',
            'indoor_fireplace',
        ],
        self::HOME_SAFETY->value => [
            'smoke_alarm',
            'carbon_monoxide_alarm',
            'fire_extinguisher',
            'first_aid_kit',
        ],
        self::INTERNET_OFFICE->value => [
            'ethernet_connection',
            'pocket_wifi',
            'dedicated_workspace',
            'wifi',
        ],
        self::KITCHEN_DINING->value => [
            'baking_sheet',
            'barbecue_utensils',
            'blender',
            'bread_maker',
            'coffee',
            'coffee_maker',
            'cooking_basics',
            'dining_table',
            'dishes_and_silverware',
            'dishwasher',
            'freezer',
            'hot_water_kettle',
            'kitchen',
            'kitchenette',
            'microwave',
            'mini_fridge',
            'oven',
            'refrigerator',
            'rice_maker',
            'stove',
            'toaster',
            'trash_compactor',
            'wine_glasses',
        ],
        self::LOCATION_FEATURES->value => [
            'lake_access',
            'beach_access',
            'ski_in_ski_out',
            'laundromat_nearby',
            'private_entrance',
            'resort_access',
            'waterfront',
        ],
        self::OUTDOOR->value => [
            'backyard',
            'bbq_grill',
            'beach_essentials',
            'bikes',
            'boat_slip',
            'fire_pit',
            'hammock',
            'kayak',
            'outdoor_dining_area',
            'outdoor_furniture',
            'sun_loungers',
            'patio_or_balcony',
            'outdoor_kitchen',
        ],
        self::PARKING_FACILITIES->value => [
            'elevator',
            'free_street_parking',
            'gym',
            'hockey_rink',
            'hot_tub',
            'pool',
            'private_living_room',
            'sauna',
            'single_level_home',
            'free_parking',
            'ev_charger',
            'paid_parking',
        ],
        self::SERVICES->value => [
            'breakfast',
            'cleaning_available_during_stay',
            'luggage_dropoff_allowed',
            'long_term_stays_allowed',
        ],
    ];

    protected static function getTypeKey(): string
    {
        return 'amenity_type';
    }

    protected static function getSubtypeKey(): string
    {
        return 'amenity_subtype';
    }

    public function getSubtypes(): array
    {
        return self::SUBTYPES[$this->value];
    }
}
