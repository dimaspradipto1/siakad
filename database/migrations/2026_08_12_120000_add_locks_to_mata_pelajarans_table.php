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
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->boolean('is_locked_harian')->default(false)->after('status');
            $table->boolean('is_locked_mid')->default(false)->after('is_locked_harian');
            $table->boolean('is_locked_pas')->default(false)->after('is_locked_mid');
            $table->boolean('is_locked_raport')->default(false)->after('is_locked_pas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_pelajarans', function (Blueprint $table) {
            $table->dropColumn(['is_locked_harian', 'is_locked_mid', 'is_locked_pas', 'is_locked_raport']);
        });
    }
};
