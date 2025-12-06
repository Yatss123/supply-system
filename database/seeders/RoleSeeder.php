<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'description' => 'Super Administrator with full system access'
            ],
            [
                'name' => Role::ADMIN,
                'description' => 'Administrator with management privileges'
            ],
            [
                'name' => Role::USER,
                'description' => 'Regular user with basic access'
            ],
            [
                'name' => Role::STUDENT,
                'description' => 'Student with department-specific access'
            ],
            [
                'name' => Role::ADVISER,
                'description' => 'Academic adviser with department oversight'
            ],
            [
                'name' => Role::DEAN,
                'description' => 'Department head with full departmental authority'
            ]
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
