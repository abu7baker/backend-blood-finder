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
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->foreign(['hospital_id'])->references(['id'])->on('hospitals')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['requester_id'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blood_requests', function (Blueprint $table) {
            $table->dropForeign('blood_requests_hospital_id_foreign');
            $table->dropForeign('blood_requests_requester_id_foreign');
        });
    }
};
