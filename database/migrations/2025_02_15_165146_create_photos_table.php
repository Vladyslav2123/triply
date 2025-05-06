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
        Schema::create('photos', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Storage info
            $table->string('url');
            $table->string('disk')->default('s3');
            $table->string('directory')->nullable();
            $table->unsignedInteger('size')->nullable()->comment('Size in bytes');

            // Polymorphic relationship (User, Listing, Experience)
            $table->ulidMorphs('photoable');

            // Image metadata
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            // Timestamps
            $table->timestamp('uploaded_at');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('uploaded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
