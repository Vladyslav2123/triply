<?php

namespace App\Enums\Traits;

trait EnumHelper
{
    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn ($value) => [$value => self::formatLabel($value)])
            ->toArray();
    }

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    protected static function formatLabel(string $value): string
    {
        return ucwords(str_replace('_', ' ', $value));
    }

    public static function labels(): array
    {
        return array_map(fn ($value) => self::formatLabel($value), self::all());
    }

    public function label(): string
    {
        return self::formatLabel($this->value);
    }
}
