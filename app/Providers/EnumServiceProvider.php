<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class EnumServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Blueprint::macro('enumCustom', function ($table, $column, $enumClass) {
            $enumName = strtolower((new ReflectionClass($enumClass))->getShortName()).'_enum';

            $cases = $enumClass::cases();

            $default = $cases[0]->value;

            $enumValuesString = implode("', '", array_map(
                static fn ($case) => $case->value,
                $cases
            ));

            DB::statement("
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = '$enumName') THEN
                        CREATE TYPE $enumName AS ENUM('$enumValuesString');
                    END IF;
                END $$;
            ");

            DB::statement("ALTER TABLE $table ADD COLUMN $column $enumName DEFAULT '$default' NOT NULL");
        });
    }
}
