<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_users', function (Blueprint $table) {
            $table->id();

            // 🩸 طلب الدم
            $table->foreignId('blood_request_id')
                  ->constrained('blood_requests')
                  ->cascadeOnDelete();

            // 👤 المستخدم (المتبرع)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // دوره في الطلب
            $table->string('role_in_request')->default('donor');

            // حالة التفاعل
            $table->enum('status', [
                'pending',   // تم الإشعار
                'accepted',  // وافق
                'rejected',  // رفض
                'cancelled', // ألغي بعد قبول شخص آخر
            ])->default('pending');

            // وقت الرد
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            // 🚫 منع تكرار نفس المتبرع لنفس الطلب
            $table->unique(['blood_request_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_users');
    }
};
