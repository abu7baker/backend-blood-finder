<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('blood_request_id')
                ->constrained('blood_requests')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('role_in_request')->default('donor');

            $table->enum('status', [
                'pending',      // لم يرد
                'accepted',     // وافق
                'unavailable',  // غير متاح
            ])->default('pending');

            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->unique(['blood_request_id', 'user_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('request_users');
    }
};
