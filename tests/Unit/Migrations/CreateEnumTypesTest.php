<?php

namespace Tests\Unit\Migrations;

use App\Enums\BookingDeadline;
use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateEnumTypesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Перевіряє, що міграція створює всі необхідні enum типи
     */
    public function test_migration_creates_enum_types(): void
    {
        // $this->markTestSkipped('Skipping test that requires database connection');

        Artisan::call('migrate:fresh');

        $enumTypes = $this->getEnumTypes();

        $this->assertContains('experiencestatus_enum', $enumTypes);
        $this->assertContains('listingstatus_enum', $enumTypes);
        $this->assertContains('reservationstatus_enum', $enumTypes);
        $this->assertContains('bookingdeadline_enum', $enumTypes);
    }

    /**
     * Отримує список всіх enum типів в базі даних
     */
    private function getEnumTypes(): array
    {
        $result = DB::select('
            SELECT typname
            FROM pg_type
            JOIN pg_enum ON pg_enum.enumtypid = pg_type.oid
            GROUP BY typname
        ');

        return array_map(static fn ($item) => $item->typname, $result);
    }

    /**
     * Перевіряє, що enum типи містять правильні значення
     */
    public function test_enum_types_have_correct_values(): void
    {
        // $this->markTestSkipped('Skipping test that requires database connection');

        Artisan::call('migrate:fresh');

        $experienceStatusValues = $this->getEnumValues('experiencestatus_enum');
        foreach (ExperienceStatus::cases() as $case) {
            $this->assertContains($case->value, $experienceStatusValues);
        }

        $listingStatusValues = $this->getEnumValues('listingstatus_enum');
        foreach (ListingStatus::cases() as $case) {
            $this->assertContains($case->value, $listingStatusValues);
        }

        $reservationStatusValues = $this->getEnumValues('reservationstatus_enum');
        foreach (ReservationStatus::cases() as $case) {
            $this->assertContains($case->value, $reservationStatusValues);
        }

        $bookingDeadlineValues = $this->getEnumValues('bookingdeadline_enum');
        foreach (BookingDeadline::cases() as $case) {
            $this->assertContains((string) $case->value, $bookingDeadlineValues);
        }
    }

    /**
     * Отримує значення для конкретного enum типу
     */
    private function getEnumValues(string $enumType): array
    {
        $result = DB::select('
            SELECT e.enumlabel
            FROM pg_enum e
            JOIN pg_type t ON e.enumtypid = t.oid
            WHERE t.typname = ?
            ORDER BY e.enumsortorder
        ', [$enumType]);

        return array_map(static fn ($item) => $item->enumlabel, $result);
    }

    /**
     * Перевіряє, що міграція додає нові значення до існуючих enum типів
     */
    public function test_migration_adds_new_values_to_existing_enum_types(): void
    {
        // $this->markTestSkipped('Skipping test that requires database connection');

        Artisan::call('migrate:fresh');

        DB::statement('DROP TYPE IF EXISTS test_enum');
        DB::statement("CREATE TYPE test_enum AS ENUM('value1', 'value2')");

        Artisan::call('migrate', ['--path' => 'database/migrations/0001_00_01_000000_create_enum_types.php', '--force' => true]);

        $enumTypes = $this->getEnumTypes();
        $this->assertContains('experiencestatus_enum', $enumTypes);
        $this->assertContains('listingstatus_enum', $enumTypes);
        $this->assertContains('reservationstatus_enum', $enumTypes);
    }
}
