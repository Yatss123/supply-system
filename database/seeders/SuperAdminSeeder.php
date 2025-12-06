<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the super admin role
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        if (!$superAdminRole) {
            $this->command->error('Super Admin role not found. Please run RoleSeeder first.');
            return;
        }

        // Create super admin user if it doesn't exist
        $superAdmin = User::where('email', 'superadmin@example.com')->first();
        
        if (!$superAdmin) {
            User::create([
                'name' => 'Super Administrator',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'email_verified_at' => now(),
            ]);
            
            $this->command->info('Super Admin user created successfully.');
            $this->command->info('Email: superadmin@example.com');
            $this->command->info('Password: password');
        } else {
            $this->command->info('Super Admin user already exists.');
        }
    }
}
