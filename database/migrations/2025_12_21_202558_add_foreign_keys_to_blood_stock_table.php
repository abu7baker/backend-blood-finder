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
        Schema::table('blood_stock', function (Blueprint $table) {
            $table->foreign(['hospital_id'])->references(['id'])->on('hospitals')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_stock', function (Blueprint $table) {
            $table->dropForeign('blood_stock_hospital_id_foreign');
        });
    }
};
