<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // الحصول على IDs للأدوار
        $adminRole     = Role::where('name', 'admin')->first()->id;
        $hospitalRole  = Role::where('name', 'hospital')->first()->id;
        $donorRole     = Role::where('name', 'donor')->first()->id;
        $patientRole   = Role::where('name', 'patient')->first()->id;

        // ---- 1. Admin account ----
        User::firstOrCreate(
            ['email' => 'admin@bloodfinder.com'],
            [
                'full_name'            => 'System Admin',
                'phone'                => '777000001',
                'password'             => Hash::make('123456'),
                'gender'               => 'male',
                'city'                 => 'Sana\'a',
                'blood_type'           => 'O+',
                'donation_eligibility' => 'eligible',
                'role_id'              => $adminRole
            ]
        );

        // ---- 2. Hospital Staff account ----
        User::firstOrCreate(
            ['email' => 'hospital@bloodfinder.com'],
            [
                'full_name'            => 'Hospital Staff',
                'phone'                => '777000002',
                'password'             => Hash::make('123456'),
                'gender'               => 'female',
                'city'                 => 'Sana\'a',
                'blood_type'           => 'A+',
                'donation_eligibility' => 'eligible',
                'role_id'              => $hospitalRole
            ]
        );

        // ---- 3. Donor account ----
        User::firstOrCreate(
            ['email' => 'donor@bloodfinder.com'],
            [
                'full_name'            => 'Regular Donor',
                'phone'                => '777000003',
                'password'             => Hash::make('123456'),
                'gender'               => 'male',
                'city'                 => 'Aden',
                'blood_type'           => 'B+',
                'last_donation_date'   => now()->subMonths(4),
                'donation_eligibility' => 'eligible',
                'role_id'              => $donorRole
            ]
        );

        // ---- 4. Patient account ----
        User::firstOrCreate(
            ['email' => 'patient@bloodfinder.com'],
            [
                'full_name'            => 'Test Patient',
                'phone'                => '777000004',
                'password'             => Hash::make('123456'),
                'gender'               => 'female',
                'city'                 => 'Ibb',
                'blood_type'           => 'O-',
                'donation_eligibility' => 'not_eligible',
                'role_id'              => $patientRole
            ]
        );
    }
}
