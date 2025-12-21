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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('full_name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('email_verification_code', 6)->nullable()->index('idx_email_verification_code');
            $table->dateTime('email_verification_expires_at')->nullable();
            $table->string('password');
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('city')->nullable();
            $table->string('blood_type')->nullable();
            $table->string('google_id')->nullable();
            $table->string('chronic_disease')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->date('last_donation_date')->nullable();
            $table->string('donation_eligibility')->default('eligible');
            $table->string('status')->default('active');
            $table->boolean('is_verified')->default(false);
            $table->string('otp')->nullable();
            $table->unsignedBigInteger('role_id')->index('users_role_id_foreign');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->string('fcm_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
