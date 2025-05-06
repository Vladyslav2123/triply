<?php

use App\Enums\BookingDeadline;
use App\Enums\Drink;
use App\Enums\EducationLevel;
use App\Enums\Equipment;
use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Food;
use App\Enums\Gender;
use App\Enums\Interest;
use App\Enums\Language;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\LocationType;
use App\Enums\NoticeType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PhysicalActivityLevel;
use App\Enums\PropertyType;
use App\Enums\ReportStatus;
use App\Enums\ReservationStatus;
use App\Enums\SkillLevel;
use App\Enums\Ticket;
use App\Enums\Transport;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumClasses = [
            BookingDeadline::class,
            Drink::class,
            EducationLevel::class,
            Equipment::class,
            ExperienceStatus::class,
            ExperienceType::class,
            Food::class,
            Gender::class,
            Interest::class,
            Language::class,
            ListingStatus::class,
            ListingType::class,
            LocationType::class,
            NoticeType::class,
            PaymentMethod::class,
            PaymentStatus::class,
            PhysicalActivityLevel::class,
            PropertyType::class,
            ReportStatus::class,
            ReservationStatus::class,
            SkillLevel::class,
            Ticket::class,
            Transport::class,
            UserRole::class,
        ];

        foreach ($enumClasses as $enumClass) {
            $this->createOrUpdateEnumType($enumClass);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    /**
     * Створює або оновлює enum тип в PostgreSQL
     * Використовує логіку з EnumServiceProvider
     */
    private function createOrUpdateEnumType(string $enumClass): void
    {
        // Створюємо тимчасову таблицю для використання макросу enumCustom
        $tempTableName = 'temp_enum_table_'.strtolower((new ReflectionClass($enumClass))->getShortName());

        // Створюємо тимчасову таблицю
        Schema::create($tempTableName, function (Blueprint $table) {
            $table->id();
        });

        // Використовуємо макрос enumCustom для створення enum типу
        Schema::table($tempTableName, function (Blueprint $table) use ($enumClass, $tempTableName) {
            $table->enumCustom($tempTableName, 'status', $enumClass);
        });

        // Видаляємо тимчасову таблицю
        Schema::dropIfExists($tempTableName);
    }
};
