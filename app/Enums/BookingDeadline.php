<?php

namespace App\Enums;

use App\Enums\Traits\EnumHelper;
use InvalidArgumentException;

enum BookingDeadline: int
{
    use EnumHelper;

    case ZERO_HOURS = 0;
    case ONE_HOUR = 1;
    case TWO_HOURS = 2;
    case THREE_HOURS = 3;
    case FOUR_HOURS = 4;
    case SIX_HOURS = 6;
    case EIGHT_HOURS = 8;
    case TEN_HOURS = 10;
    case TWELVE_HOURS = 12;
    case SIXTEEN_HOURS = 16;
    case EIGHTEEN_HOURS = 18;
    case ONE_DAY = 24;
    case TWO_DAYS = 48;
    case THREE_DAYS = 72;
    case FOUR_DAYS = 96;
    case FIVE_DAYS = 120;
    case ONE_WEEK = 168;

    public static function fromHours(int $hours): self
    {
        return match ($hours) {
            0 => self::ZERO_HOURS,
            1 => self::ONE_HOUR,
            2 => self::TWO_HOURS,
            3 => self::THREE_HOURS,
            4 => self::FOUR_HOURS,
            6 => self::SIX_HOURS,
            8 => self::EIGHT_HOURS,
            10 => self::TEN_HOURS,
            12 => self::TWELVE_HOURS,
            16 => self::SIXTEEN_HOURS,
            18 => self::EIGHTEEN_HOURS,
            24 => self::ONE_DAY,
            48 => self::TWO_DAYS,
            72 => self::THREE_DAYS,
            96 => self::FOUR_DAYS,
            120 => self::FIVE_DAYS,
            168 => self::ONE_WEEK,
            default => throw new InvalidArgumentException("Недійсний час: $hours"),
        };
    }

    public function toHours(): int
    {
        return $this->value;
    }
}
