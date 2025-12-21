<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('donor_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('hospital_id')
                ->constrained('hospitals')
                ->cascadeOnDelete();

            $table->foreignId('request_id')
                ->nullable()
                ->constrained('blood_requests')
                ->nullOnDelete();

            $table->string('blood_type');
            $table->integer('units_donated')->default(1);

            $table->timestamp('donated_at')->useCurrent();

            $table->string('status')->default('completed');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
