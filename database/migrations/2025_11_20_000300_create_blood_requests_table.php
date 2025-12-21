<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_requests', function (Blueprint $table) {
    $table->id();

    // من قام بالطلب (مستخدم – أو حساب مستشفى)
    $table->foreignId('requester_id')
        ->constrained('users')
        ->cascadeOnDelete();

    // المستشفى المستهدفة
    $table->foreignId('hospital_id')
        ->constrained('hospitals')
        ->cascadeOnDelete();

    $table->enum('blood_type', [
        'O+','O-','A+','A-','B+','B-','AB+','AB-'
    ]);

    $table->integer('units_requested');

    // الأولوية
    $table->enum('priority', ['normal','urgent','critical'])->default('normal');

    // حالة الطلب
    $table->enum('status', ['pending','approved','rejected','completed'])
        ->default('pending');

    $table->text('notes')->nullable();

    // معلومات المريض
    $table->string('patient_name')->nullable();
    $table->string('patient_gender')->nullable();
    $table->integer('patient_age')->nullable();
    $table->string('doctor_name')->nullable();
    $table->string('diagnosis')->nullable();

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('blood_requests');
    }
};
