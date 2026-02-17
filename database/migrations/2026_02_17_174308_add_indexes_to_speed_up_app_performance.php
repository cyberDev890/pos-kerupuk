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
            $table->index('tanggal');
            $table->index('status');
            $table->index('deleted_at');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('stok');
            $table->index('stok_min');
            $table->index('is_active');
            $table->index('deleted_at');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('status');
            $table->index('deleted_at');
        });

        Schema::table('product_returns', function (Blueprint $table) {
            $table->index('tanggal');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stok']);
            $table->dropIndex(['stok_min']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('product_returns', function (Blueprint $table) {
            $table->dropIndex(['tanggal']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
