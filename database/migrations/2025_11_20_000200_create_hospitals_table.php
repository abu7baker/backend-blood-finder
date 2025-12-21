<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();

            // كل مستشفى مرتبط بحساب مستخدم واحد
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('name');               // اسم المستشفى
            $table->string('city');               // المدينة
            $table->string('location')->nullable(); // العنوان / الموقع التفصيلي
            $table->string('status')->default('pending'); // verified / pending / blocked

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
