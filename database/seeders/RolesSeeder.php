<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'System Administrator'
            ],
            [
                'name' => 'hospital',
                'description' => 'Hospital Staff Account'
            ],
            [
                'name' => 'donor',
                'description' => 'User who donates blood'
            ],
            [
                'name' => 'patient',
                'description' => 'User who requests blood'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
