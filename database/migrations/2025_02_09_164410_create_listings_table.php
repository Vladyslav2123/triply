<?php

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\NoticeType;
use App\Enums\PropertyType;
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
        Schema::create('listings', static function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->jsonb('seo')->nullable();

            // Basic properties
            $table->string('title', 32);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->float('rating')->default(0)->unsigned();
            $table->integer('reviews_count')->default(0)->unsigned();

            // Listing specific
            $table->jsonb('description');
            $table->unsignedBigInteger('price_per_night')->comment('Сума в копійках');
            $table->jsonb('discounts')->nullable();
            $table->jsonb('accept_guests')->nullable();
            $table->jsonb('rooms_rules')->nullable();
            $table->string('subtype');
            $table->jsonb('amenities')->nullable();
            $table->jsonb('accessibility_features')->nullable();
            $table->jsonb('availability_settings')->nullable();
            $table->jsonb('location');
            $table->jsonb('house_rules')->nullable();
            $table->jsonb('guest_safety')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('listings', static function (Blueprint $table) {
            $table->enumCustom('listings', 'type', PropertyType::class);
            $table->enumCustom('listings', 'listing_type', ListingType::class);
            $table->enumCustom('listings', 'advance_notice_type', NoticeType::class);
            $table->enumCustom('listings', 'status', ListingStatus::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
