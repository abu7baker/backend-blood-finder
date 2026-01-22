<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // حذف القيد القديم
        DB::statement("
            ALTER TABLE blood_requests
            DROP CONSTRAINT IF EXISTS blood_requests_status_check
        ");

        // إضافة القيد الجديد مع in_progress
        DB::statement("
            ALTER TABLE blood_requests
            ADD CONSTRAINT blood_requests_status_check
            CHECK (
                status IN (
                    'pending',
                    'approved',
                    'in_progress',
                    'completed',
                    'cancelled'
                )
            )
        ");
    }

    public function down(): void
    {
        // الرجوع للحالات القديمة
        DB::statement("
            ALTER TABLE blood_requests
            DROP CONSTRAINT IF EXISTS blood_requests_status_check
        ");

        DB::statement("
            ALTER TABLE blood_requests
            ADD CONSTRAINT blood_requests_status_check
            CHECK (
                status IN (
                    'pending',
                    'approved',
                    'completed',
                    'cancelled'
                )
            )
        ");
    }
};
