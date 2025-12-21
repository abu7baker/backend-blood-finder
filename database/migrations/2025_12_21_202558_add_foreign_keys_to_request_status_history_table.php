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
        Schema::table('request_status_history', function (Blueprint $table) {
            $table->foreign(['changed_by'])->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['request_id'])->references(['id'])->on('blood_requests')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_status_history', function (Blueprint $table) {
            $table->dropForeign('request_status_history_changed_by_foreign');
            $table->dropForeign('request_status_history_request_id_foreign');
        });
    }
};
