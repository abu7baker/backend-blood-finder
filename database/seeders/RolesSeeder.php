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
                'description' => 'System Administrator with full access'
            ],
            [
                'name' => 'hospital',
                'description' => 'Hospital account for managing blood requests and donations'
            ],
            [
                'name' => 'user',
                'description' => 'Regular user (Donor / Patient)'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
