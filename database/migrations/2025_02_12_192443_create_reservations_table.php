<?php

use App\Enums\ReservationStatus;
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
        Schema::create('reservations', static function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('guest_id')->constrained('users')->cascadeOnDelete();

            $table->ulidMorphs('reservationable');

            $table->date('check_in')->index();
            $table->date('check_out')->index();
            $table->unsignedBigInteger('total_price')->comment('Сума в копійках');

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('reservations', static function (Blueprint $table) {
            $table->enumCustom('reservations', 'status', ReservationStatus::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');

    }
};
