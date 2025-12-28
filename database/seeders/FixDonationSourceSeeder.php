<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Donation;

class FixDonationSourceSeeder extends Seeder
{
    public function run()
    {
        // التبرعات المرتبطة بطلب دم
        Donation::whereNotNull('request_id')
            ->update(['source' => 'blood_request']);

        // التبرعات المباشرة (موعد)
        Donation::whereNull('request_id')
            ->update(['source' => 'appointment']);
    }
}
