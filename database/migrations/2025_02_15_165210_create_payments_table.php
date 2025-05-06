<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('reservation_id')->constrained('reservations')->cascadeOnDelete();

            // Payment details
            $table->unsignedBigInteger('amount')->comment('Сума в копійках');
            $table->string('currency', 3)->default('USD');
            $table->timestamp('paid_at')->nullable();

            // Transaction details
            $table->string('transaction_id')->nullable()->unique();
            $table->json('transaction_details')->nullable();

            // Refund tracking
            $table->unsignedBigInteger('refunded_amount')->nullable()->comment('Сума повернення в копійках');
            $table->timestamp('refunded_at')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enumCustom('payments', 'payment_method', PaymentMethod::class);
            $table->enumCustom('payments', 'status', PaymentStatus::class);
            // Indexes
            $table->index('paid_at');
            $table->index(['created_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
