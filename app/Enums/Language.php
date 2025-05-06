<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;

enum Language: string
{
    use EnumHelper;

    case ENGLISH = 'english';
    case SPANISH = 'spanish';
    case FRENCH = 'french';
    case GERMAN = 'german';
    case CHINESE = 'chinese';
    case JAPANESE = 'japanese';
    case KOREAN = 'korean';
    case ITALIAN = 'italian';
    case PORTUGUESE = 'portuguese';
    case RUSSIAN = 'russian';
    case UKRAINIAN = 'ukrainian';
    case DUTCH = 'dutch';
    case SWEDISH = 'swedish';
    case NORWEGIAN = 'norwegian';
    case FINNISH = 'finnish';
    case ARABIC = 'arabic';
    case HINDI = 'hindi';
    case TURKISH = 'turkish';
    case POLISH = 'polish';
    case GREEK = 'greek';
    case HEBREW = 'hebrew';
    case THAI = 'thai';
    case VIETNAMESE = 'vietnamese';
}
