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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('bayar', 15, 2)->default(0)->after('total_harga');
            $table->decimal('remaining_debt', 15, 2)->default(0)->after('bayar');
            $table->string('status')->default('selesai')->after('remaining_debt'); // selesai, pending
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['bayar', 'remaining_debt', 'status']);
        });
    }
};
