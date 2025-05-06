<?php

namespace Tests\Unit\Migrations;

use App\Enums\ExperienceStatus;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnumTypesMigrationWithProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Перевіряє, що міграція використовує EnumServiceProvider для створення enum типів
     */
    #[Test]
    public function migration_uses_enum_service_provider(): void
    {
        Artisan::call('migrate:fresh');

        $enumTypes = $this->getEnumTypes();
        $this->assertContains('experiencestatus_enum', $enumTypes);
        $this->assertContains('listingstatus_enum', $enumTypes);
        $this->assertContains('reservationstatus_enum', $enumTypes);

        $experienceStatusValues = $this->getEnumValues('experiencestatus_enum');
        foreach (ExperienceStatus::cases() as $case) {
            $this->assertContains($case->value, $experienceStatusValues);
        }

        Schema::create('test_experiences', function ($table) {
            $table->id();
            $table->string('name');
        });

        DB::statement("ALTER TABLE test_experiences ADD COLUMN status experiencestatus_enum DEFAULT 'draft' NOT NULL");

        $this->assertTrue(Schema::hasColumn('test_experiences', 'status'));

        DB::table('test_experiences')->insert([
            'name' => 'Test Experience',
            'status' => ExperienceStatus::DRAFT->value,
        ]);

        $experience = DB::table('test_experiences')->first();
        $this->assertEquals(ExperienceStatus::DRAFT->value, $experience->status);

        try {
            DB::table('test_experiences')->insert([
                'name' => 'Invalid Experience',
                'status' => 'invalid_status',
            ]);
            $this->fail('Вдалося вставити невалідне значення enum');
        } catch (Exception $e) {
            $this->assertStringContainsString('invalid_status', $e->getMessage());
        }
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
     * Перевіряє, що міграція правильно оновлює існуючі enum типи
     */
    #[Test]
    public function migration_updates_existing_enum_types(): void
    {
        DB::statement('DROP TYPE IF EXISTS test_status_enum');
        DB::statement("CREATE TYPE test_status_enum AS ENUM('value1', 'value2')");

        Artisan::call('migrate:fresh');

        $enumTypes = $this->getEnumTypes();
        $this->assertContains('experiencestatus_enum', $enumTypes);
        $this->assertContains('listingstatus_enum', $enumTypes);
        $this->assertContains('reservationstatus_enum', $enumTypes);

        $this->assertContains('test_status_enum', $enumTypes);

        $testStatusValues = $this->getEnumValues('test_status_enum');
        $this->assertContains('value1', $testStatusValues);
        $this->assertContains('value2', $testStatusValues);
    }
}
