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
        Schema::create('donations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('donor_id')->index('donations_donor_id_foreign');
            $table->unsignedBigInteger('hospital_id')->index('donations_hospital_id_foreign');
            $table->unsignedBigInteger('request_id')->nullable()->index('donations_request_id_foreign');
            $table->string('blood_type');
            $table->integer('units_donated')->default(1);
            $table->timestamp('donated_at')->useCurrent();
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
