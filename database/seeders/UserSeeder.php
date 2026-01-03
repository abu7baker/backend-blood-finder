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
        $adminRole    = Role::where('name', 'admin')->first()->id;
        $hospitalRole = Role::where('name', 'hospital')->first()->id;
        $userRole     = Role::where('name', 'user')->first()->id;

        // 1️⃣ Admin Account
        User::updateOrCreate(
            ['email' => 'admin@bloodfinder.com'],
            [
                'full_name'            => 'System Admin',
                'phone'                => '777000001',
                'password'             => Hash::make('123456'),
                'gender'               => 'male',
                'city'                 => 'Sana\'a',
                'blood_type'           => 'O+',
                'donation_eligibility' => 'eligible',
                'role_id'              => $adminRole,
                'is_verified'          => 1,
                'status'               => 'active',
            ]
        );

        // 2️⃣ Hospital Staff Account
        User::updateOrCreate(
            ['email' => 'hospital@bloodfinder.com'],
            [
                'full_name'            => 'Hospital Staff',
                'phone'                => '777000002',
                'password'             => Hash::make('123456'),
                'gender'               => 'female',
                'city'                 => 'Sana\'a',
                'blood_type'           => 'A+',
                'donation_eligibility' => 'eligible',
                'role_id'              => $hospitalRole,
                'is_verified'          => 1,
                'status'               => 'active',
            ]
        );

        // 3️⃣ Donor Account (User Role)
        User::updateOrCreate(
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
                'role_id'              => $userRole,
                'is_verified'          => 1,
                'status'               => 'active',
            ]
        );

        // 4️⃣ Patient Account (User Role)
        User::updateOrCreate(
            ['email' => 'patient@bloodfinder.com'],
            [
                'full_name'            => 'Test Patient',
                'phone'                => '777000004',
                'password'             => Hash::make('123456'),
                'gender'               => 'female',
                'city'                 => 'Ibb',
                'blood_type'           => 'O-',
                'donation_eligibility' => 'not_eligible',
                'role_id'              => $userRole,
                'is_verified'          => 1,
                'status'               => 'active',
            ]
        );
    }
}
