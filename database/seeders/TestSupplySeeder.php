<?php

namespace Database\Seeders;

use App\Models\Supply;
use App\Models\Category;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSupplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test categories
        $categories = [
            ['name' => 'Office Supplies', 'description' => 'General office supplies'],
            ['name' => 'Electronics', 'description' => 'Electronic equipment and devices'],
            ['name' => 'Furniture', 'description' => 'Office furniture and fixtures'],
            ['name' => 'Stationery', 'description' => 'Writing and paper materials'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                ['description' => $categoryData['description']]
            );
        }

        // Create test department
        $department = Department::create([
            'department_name' => 'Information Technology',
        ]);

        // Create test user if not exists
        $userRole = Role::where('name', 'user')->first();
        $adminRole = Role::where('name', 'admin')->first();
        
        if ($userRole) {
            User::firstOrCreate(
                ['email' => 'testuser@example.com'],
                [
                    'name' => 'Test User',
                    'password' => Hash::make('password'),
                    'role_id' => $userRole->id,
                    'department_id' => $department->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        if ($adminRole) {
            User::firstOrCreate(
                ['email' => 'testadmin@example.com'],
                [
                    'name' => 'Test Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $adminRole->id,
                    'department_id' => $department->id,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Create test supplies with different types and statuses
        $supplies = [
            [
                'name' => 'Ballpoint Pen (Blue)',
                'description' => 'Standard blue ballpoint pen for office use',
                'quantity' => 50,
                'unit' => 'pieces',
                'minimum_stock_level' => 10,
                'supply_type' => 'consumable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Office Supplies', 'Stationery']
            ],
            [
                'name' => 'Laptop Computer',
                'description' => 'Dell Latitude 5520 - 15.6" Business Laptop',
                'quantity' => 5,
                'unit' => 'units',
                'minimum_stock_level' => 2,
                'supply_type' => 'borrowable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Electronics']
            ],
            [
                'name' => 'Office Chair',
                'description' => 'Ergonomic office chair with adjustable height',
                'quantity' => 10,
                'unit' => 'units',
                'minimum_stock_level' => 3,
                'supply_type' => 'grantable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Furniture']
            ],
            [
                'name' => 'Printer Paper',
                'description' => 'A4 size white printer paper (500 sheets per ream)',
                'quantity' => 2,
                'unit' => 'reams',
                'minimum_stock_level' => 5,
                'supply_type' => 'consumable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Office Supplies', 'Stationery']
            ],
            [
                'name' => 'Damaged Projector',
                'description' => 'Epson projector - needs repair',
                'quantity' => 1,
                'unit' => 'units',
                'minimum_stock_level' => 1,
                'supply_type' => 'borrowable',
                'status' => 'damaged',
                'has_variants' => false,
                'categories' => ['Electronics']
            ]
        ];

        foreach ($supplies as $supplyData) {
            $categoryNames = $supplyData['categories'];
            unset($supplyData['categories']);

            $supply = Supply::firstOrCreate(
                ['name' => $supplyData['name']],
                $supplyData
            );

            // Attach categories
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id');
            $supply->categories()->sync($categoryIds);
        }

        $this->command->info('Test supplies created successfully!');
        $this->command->info('Test users created:');
        $this->command->info('- testuser@example.com (password: password)');
        $this->command->info('- testadmin@example.com (password: password)');
    }
}
