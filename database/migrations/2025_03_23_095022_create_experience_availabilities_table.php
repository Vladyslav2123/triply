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
        Schema::create('experience_availabilities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('experience_id')
                ->constrained('experiences')
                ->cascadeOnDelete();

            // DateTime for specific time slots
            $table->datetime('date')->index();
            $table->boolean('is_available')->default(true)->index();

            // Capacity management
            $table->unsignedSmallInteger('spots_available')
                ->nullable()
                ->comment('Available spots for this time slot');

            // Price override for specific dates/events
            $table->unsignedBigInteger('price_override')
                ->nullable()
                ->comment('Сума в копійках');

            // System fields
            $table->timestamps();

            // Ensure unique datetime per experience
            $table->unique(['experience_id', 'date']);

            // Index for availability checks
            $table->index(['experience_id', 'date', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_availabilities');
    }
};
