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
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('donor_id')->index('appointments_donor_id_foreign');
            $table->unsignedBigInteger('hospital_id')->index('appointments_hospital_id_foreign');
            $table->unsignedBigInteger('request_id')->nullable()->index('appointments_request_id_foreign');
            $table->dateTime('date_time');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
