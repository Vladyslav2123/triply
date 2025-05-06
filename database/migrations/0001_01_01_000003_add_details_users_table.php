<?php

use App\Enums\UserRole;
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
        Schema::table('users', static function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->dropColumn('name');
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->ulid('id')->primary()->after('email');
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->string('slug')->unique();
            $table->string('phone')->unique()->nullable();
            $table->enumCustom('users', 'role', UserRole::class);
            $table->boolean('is_banned')->default(false);
        });

        Schema::table('sessions', static function (Blueprint $table) {
            $table->dropIndex('sessions_user_id_index');
            $table->dropColumn('user_id');
        });

        Schema::table('sessions', static function (Blueprint $table) {
            $table->foreignUlid('user_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'slug', 'is_banned']);
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
        });

        Schema::table('users', static function (Blueprint $table) {
            $table->id()->primary();
        });

        Schema::table('sessions', static function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->foreignId('user_id')->nullable()->index();
        });
    }
};
