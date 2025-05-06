<?php

namespace Tests\Unit\Migrations;

use App\Enums\ExperienceStatus;
use App\Enums\ListingStatus;
use App\Enums\ReservationStatus;
use Tests\TestCase;

class EnumTypesMigrationStructureTest extends TestCase
{
    /**
     * Перевіряє, що міграція містить всі необхідні enum класи
     */
    public function test_migration_contains_all_required_enum_classes(): void
    {
        $migrationPath = database_path('migrations/0001_00_01_000000_create_enum_types.php');
        $this->assertFileExists($migrationPath);

        $migrationContent = file_get_contents($migrationPath);

        $this->assertStringContainsString('use App\Enums\ExperienceStatus;', $migrationContent);
        $this->assertStringContainsString('use App\Enums\ListingStatus;', $migrationContent);
        $this->assertStringContainsString('use App\Enums\ReservationStatus;', $migrationContent);
        $this->assertStringContainsString('use App\Enums\BookingDeadline;', $migrationContent);

        $this->assertStringContainsString('ExperienceStatus::class', $migrationContent);
        $this->assertStringContainsString('ListingStatus::class', $migrationContent);
        $this->assertStringContainsString('ReservationStatus::class', $migrationContent);
        $this->assertStringContainsString('BookingDeadline::class', $migrationContent);
    }

    /**
     * Перевіряє, що міграція використовує EnumServiceProvider
     */
    public function test_migration_uses_enum_service_provider(): void
    {
        $migrationPath = database_path('migrations/0001_00_01_000000_create_enum_types.php');
        $migrationContent = file_get_contents($migrationPath);

        $this->assertStringContainsString('use Illuminate\Database\Schema\Blueprint;', $migrationContent);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Schema;', $migrationContent);

        $this->assertStringContainsString('$table->enumCustom(', $migrationContent);
    }

    /**
     * Перевіряє, що enum класи містять правильні значення
     */
    public function test_enum_classes_have_correct_values(): void
    {
        $experienceStatusValues = array_map(static fn ($case) => $case->value, ExperienceStatus::cases());
        $this->assertContains('draft', $experienceStatusValues);
        $this->assertContains('pending', $experienceStatusValues);
        $this->assertContains('published', $experienceStatusValues);

        $listingStatusValues = array_map(static fn ($case) => $case->value, ListingStatus::cases());
        $this->assertContains('draft', $listingStatusValues);
        $this->assertContains('pending', $listingStatusValues);
        $this->assertContains('published', $listingStatusValues);

        $reservationStatusValues = array_map(static fn ($case) => $case->value, ReservationStatus::cases());
        $this->assertContains('pending', $reservationStatusValues);
        $this->assertContains('confirmed', $reservationStatusValues);
        $this->assertContains('completed', $reservationStatusValues);
    }
}
