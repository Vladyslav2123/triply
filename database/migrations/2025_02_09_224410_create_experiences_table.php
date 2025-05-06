<?php

use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\LocationType;
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
        Schema::create('experiences', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->jsonb('seo')->nullable();

            // Basic properties
            $table->string('title', 32); // Короткий опис враження
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->float('rating')->default(0)->unsigned();
            $table->integer('reviews_count')->default(0)->unsigned();

            // Experience specific
            $table->jsonb('location'); // У якому місті ви бажаєте організувати враження?
            $table->jsonb('languages'); // Мови, на яких ви будете проводити враження?
            $table->string('sub_category'); // Підкатегорія
            $table->string('reviews');
            $table->string('description'); // Опис враження (Виберіть тему, яка найкраще описує те, що очікує на гостей.)
            $table->dateTime('duration'); // Як довго триватиме враження?
            $table->string('location_note')->nullable(); // Опис розташування
            $table->string('location_subtype'); // Який тип місця ви оберете для враження? Приклад (Релігійно-культурне місце:Кладовище, Синагога)
            $table->jsonb('host_bio'); // Розкажіть гостям (і нам) більше про себе
            $table->jsonb('host_verification')->nullable()->after('host_bio'); // Інформація про верифікацію організатора
            $table->jsonb('address'); // Де ви зустрінетеся з гостями?
            $table->jsonb('host_provides')->nullable();  // Додайте інформацію про те, що ви надасте
            $table->jsonb('guest_needs')->nullable(); // Чи потрібно гостям щось принести зі собою на це враження?
            $table->jsonb('guest_requirements'); // Вимоги до гостей
            $table->string('name'); // Назва враження(60 символів)
            $table->jsonb('grouping'); // Розмір групи
            $table->dateTime('starts_at'); // Коли ви будете проводити враження?
            $table->jsonb('pricing'); // Ціна
            $table->jsonb('discounts'); // Знижки
            $table->jsonb('booking_rules'); // Правила бронювання
            $table->jsonb('cancellation_policy'); // Політика скасування
            $table->jsonb('host_licenses')->nullable()->after('host_verification'); // Інформація про ліцензію організатора

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('experiences', static function (Blueprint $table) {
            $table->enumCustom('experiences', 'category', ExperienceType::class); // Виберіть тему враження(категорію), яка найкраще описує те, що очікує на гостей.
            $table->enumCustom('experiences', 'location_type', LocationType::class); // Який тип місця ви оберете для враження? Приклад (Релігійно-культурне місце, Туристичне місце, Державний або освітній заклади, Спорт-та-здоров\'я центр, Місце розваг, Природа-відпочинок, Торгівельна зона, Історичне місце)
            $table->enumCustom('experiences', 'status', ExperienceStatus::class); // Статус враження
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};
