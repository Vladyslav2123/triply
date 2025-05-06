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
        Schema::create('reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Relations
            $table->foreignUlid('reservation_id')
                ->constrained('reservations')
                ->cascadeOnDelete();
            $table->foreignUlid('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Ratings (1-5)
            $table->decimal('overall_rating', 2, 1)
                ->unsigned()
                ->comment('1-5')
                ->index();
            $table->tinyInteger('cleanliness_rating')
                ->unsigned()
                ->comment('1-5');
            $table->tinyInteger('accuracy_rating')
                ->unsigned()
                ->comment('1-5');
            $table->tinyInteger('checkin_rating')
                ->unsigned()
                ->comment('1-5');
            $table->tinyInteger('communication_rating')
                ->unsigned()
                ->comment('1-5');
            $table->tinyInteger('location_rating')
                ->unsigned()
                ->comment('1-5');
            $table->tinyInteger('value_rating')
                ->unsigned()
                ->comment('1-5');

            // Content
            $table->text('comment')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['reservation_id', 'reviewer_id']);
            $table->index(['created_at']);

            // Ensure one review per reservation per reviewer
            $table->unique(['reservation_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
