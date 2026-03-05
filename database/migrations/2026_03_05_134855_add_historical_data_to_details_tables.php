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
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->decimal('hpp', 15, 2)->nullable()->after('harga_satuan');
            $table->decimal('conversion', 8, 2)->nullable()->after('unit_id');
            $table->string('unit_type')->nullable()->after('conversion'); // besar, kecil
        });

        Schema::table('return_details', function (Blueprint $table) {
            $table->decimal('hpp', 15, 2)->nullable()->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn(['hpp', 'conversion', 'unit_type']);
        });

        Schema::table('return_details', function (Blueprint $table) {
            $table->dropColumn('hpp');
        });
    }
};
