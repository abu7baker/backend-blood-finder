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
        Schema::table('donations', function (Blueprint $table) {
            $table->foreign(['donor_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['hospital_id'])->references(['id'])->on('hospitals')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['request_id'])->references(['id'])->on('blood_requests')->onUpdate('restrict')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign('donations_donor_id_foreign');
            $table->dropForeign('donations_hospital_id_foreign');
            $table->dropForeign('donations_request_id_foreign');
        });
    }
};
