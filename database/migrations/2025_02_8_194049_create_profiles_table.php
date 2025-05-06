<?php

use App\Enums\EducationLevel;
use App\Enums\Gender;
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
        Schema::create('profiles', static function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users', 'id')->cascadeOnDelete();

            // Основна інформація
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->boolean('is_superhost')->default(false);
            $table->float('response_speed')->default(100)->unsigned();

            // Додаткові метрики
            $table->integer('views_count')->default(0)->unsigned();
            $table->float('rating')->default(0)->unsigned();
            $table->integer('reviews_count')->default(0)->unsigned();

            // Робота та освіта
            $table->string('work', 50)->nullable();
            $table->string('job_title', 50)->nullable();
            $table->string('company', 50)->nullable();
            $table->string('school', 50)->nullable();

            // Подорожі
            $table->string('dream_destination', 50)->nullable();
            $table->jsonb('next_destinations')->nullable();
            $table->boolean('travel_history')->default(false);
            $table->string('favorite_travel_type', 30)->nullable();

            // Особисті дані
            $table->string('time_spent_on', 50)->nullable();
            $table->string('useless_skill', 50)->nullable();
            $table->string('pets', 50)->nullable();
            $table->boolean('birth_decade')->default(false);
            $table->string('favorite_high_school_song', 50)->nullable();
            $table->text('fun_fact')->nullable();
            $table->string('obsession', 50)->nullable();
            $table->string('biography_title', 50)->nullable();

            // Мови та інтереси
            $table->jsonb('languages')->nullable();
            $table->text('about')->nullable();
            $table->jsonb('interests')->nullable();

            // Місцезнаходження
            $table->jsonb('location')->nullable();

            // Соціальні мережі
            $table->string('facebook_url', 100)->nullable();
            $table->string('instagram_url', 100)->nullable();
            $table->string('twitter_url', 100)->nullable();
            $table->string('linkedin_url', 100)->nullable();

            // Налаштування
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            $table->string('preferred_language', 10)->default('uk');
            $table->string('preferred_currency', 3)->default('UAH');

            // Верифікація
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_method', 20)->nullable();

            // Системні поля
            $table->timestamp('last_active_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Індекси
            $table->index('last_active_at');
            $table->index('is_superhost');
            $table->index('is_verified');
        });

        Schema::table('profiles', static function (Blueprint $table) {
            $table->enumCustom('profiles', 'gender', Gender::class);
            $table->enumCustom('profiles', 'education_level', EducationLevel::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
