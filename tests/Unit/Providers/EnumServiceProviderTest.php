<?php

namespace Tests\Unit\Providers;

use App\Enums\ExperienceStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnumServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Перевіряє, що EnumServiceProvider правильно реєструє макрос enumCustom
     */
    #[Test]
    public function enum_service_provider_registers_enum_custom_macro(): void
    {
        $this->assertTrue(Blueprint::hasMacro('enumCustom'));
    }

    /**
     * Перевіряє, що макрос enumCustom правильно створює enum тип
     */
    #[Test]
    public function enum_custom_macro_creates_enum_type(): void
    {
        Schema::create('test_table', static function (Blueprint $table) {
            $table->id();
        });

        Schema::table('test_table', static function (Blueprint $table) {
            $table->enumCustom('test_table', 'status', ExperienceStatus::class);
        });

        $enumTypes = $this->getEnumTypes();
        $this->assertContains('experiencestatus_enum', $enumTypes);

        $this->assertTrue(Schema::hasColumn('test_table', 'status'));

        $columnType = DB::selectOne("
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'test_table' AND column_name = 'status'
        ")->data_type;

        $this->assertEquals('USER-DEFINED', $columnType);

        $enumValues = $this->getEnumValues('experiencestatus_enum');
        foreach (ExperienceStatus::cases() as $case) {
            $this->assertContains($case->value, $enumValues);
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
     * Перевіряє, що макрос enumCustom не створює дублікати enum типів
     */
    #[Test]
    public function enum_custom_macro_does_not_create_duplicate_enum_types(): void
    {
        Schema::create('test_table_1', static function (Blueprint $table) {
            $table->id();
        });

        Schema::table('test_table_1', static function (Blueprint $table) {
            $table->enumCustom('test_table_1', 'status', ExperienceStatus::class);
        });

        Schema::create('test_table_2', static function (Blueprint $table) {
            $table->id();
        });

        Schema::table('test_table_2', static function (Blueprint $table) {
            $table->enumCustom('test_table_2', 'status', ExperienceStatus::class);
        });

        $enumTypes = $this->getEnumTypes();
        $this->assertCount(1, array_filter($enumTypes, static fn ($type) => $type === 'experiencestatus_enum'));

        $this->assertTrue(Schema::hasColumn('test_table_1', 'status'));
        $this->assertTrue(Schema::hasColumn('test_table_2', 'status'));
    }
}
