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
        Schema::create('favorites', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();

            // Polymorphic relationship for Listing, Experience, etc.
            $table->ulidMorphs('favoriteable');

            // When the item was favorited
            $table->timestamp('added_at');

            // Ensure user can't favorite same item multiple times
            $table->unique(['user_id', 'favoriteable_id', 'favoriteable_type']);

            // Indexes for common queries
            $table->index(['user_id', 'added_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
