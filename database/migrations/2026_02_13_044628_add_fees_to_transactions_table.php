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
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('biaya_kirim', 15, 2)->default(0)->after('kembalian');
            $table->decimal('biaya_tambahan', 15, 2)->default(0)->after('biaya_kirim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['biaya_kirim', 'biaya_tambahan']);
        });
    }
};
