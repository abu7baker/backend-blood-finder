<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // ============= User Basic Info =============
            $table->string('full_name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');

            // ============= Extra Personal Info =============
            $table->string('age')->nullable();
            $table->string('gender')->nullable(); // male / female
            $table->string('city')->nullable();
            $table->string('blood_type')->nullable();

            $table->string('google_id')->nullable();

            // ============= Medical Info =============
            $table->string('chronic_disease')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->date('last_donation_date')->nullable();
          $table->string('donation_eligibility')->default('eligible'); // eligible / not_eligible
            // ============= System Columns =============
            $table->string('status')->default('active'); // active / inactive
            $table->boolean('is_verified')->default(false);

            // OTP for verification
            $table->string('otp')->nullable();

            // User Role
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            // Laravel default fields
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
