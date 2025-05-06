<?php

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
        Schema::create('listing_availabilities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();

            // Date and availability
            $table->date('date')->index();
            $table->boolean('is_available')->default(true)->index();

            // Price override for specific dates
            $table->unsignedBigInteger('price_override')
                ->nullable()
                ->comment('Сума в копійках');

            // System fields
            $table->timestamps();

            // Ensure unique date per listing
            $table->unique(['listing_id', 'date']);

            // Index for availability checks
            $table->index(['listing_id', 'date', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_availabilities');
    }
};
