<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE blood_requests MODIFY status ENUM('pending','approved','in_progress','rejected','completed','cancelled') DEFAULT 'pending'"
        );
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE blood_requests MODIFY status ENUM('pending','approved','rejected','completed','cancelled') DEFAULT 'pending'"
        );
    }
};
