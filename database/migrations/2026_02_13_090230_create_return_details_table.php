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
        if (!Schema::hasTable('return_details')) {
            Schema::create('return_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_return_id')->constrained('product_returns')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products');
                $table->foreignId('unit_id')->constrained('units');
                $table->decimal('jumlah', 10, 2);
                $table->decimal('harga_satuan', 15, 2);
                $table->decimal('subtotal', 15, 2);
                $table->string('unit_info')->nullable(); // Snapshot of unit info
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_details');
    }
};
