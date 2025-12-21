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
        Schema::create('blood_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('requester_id')->index('blood_requests_requester_id_foreign');
            $table->unsignedBigInteger('hospital_id')->index('blood_requests_hospital_id_foreign');
            $table->enum('blood_type', ['O+', 'O-', 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-']);
            $table->integer('units_requested');
            $table->enum('priority', ['normal', 'urgent', 'critical'])->default('normal');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('patient_name')->nullable();
            $table->string('patient_gender')->nullable();
            $table->integer('patient_age')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('diagnosis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};
