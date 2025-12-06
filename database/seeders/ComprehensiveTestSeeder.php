<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Category;
use App\Models\Supply;
use App\Models\SupplyVariant;
use App\Models\Supplier;
use App\Models\SupplyRequest;
use App\Models\BorrowedItem;
use App\Models\IssuedItem;
use App\Models\RestockRequest;
use App\Models\LoanRequest;
use App\Models\ManualReceipt;
use App\Models\InterDepartmentLoanRequest;
use App\Models\InterDepartmentBorrowedItem;
use App\Models\ProfileUpdateRequest;
use App\Models\StatusChangeRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ComprehensiveTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting comprehensive test data seeding...');

        // Create additional departments
        $departments = [
            ['department_name' => 'Computer Science', 'status' => 'active'],
            ['department_name' => 'Engineering', 'status' => 'active'],
            ['department_name' => 'Business Administration', 'status' => 'active'],
            ['department_name' => 'Liberal Arts', 'status' => 'active'],
            ['department_name' => 'Sciences', 'status' => 'inactive'],
        ];

        foreach ($departments as $deptData) {
            Department::firstOrCreate(
                ['department_name' => $deptData['department_name']],
                $deptData
            );
        }

        // Get roles
        $roles = Role::all()->keyBy('name');
        $depts = Department::all();

        // Create test users for each role
        $users = [
            [
                'name' => 'John Dean',
                'email' => 'dean@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['dean']->id,
                'department_id' => $depts->where('department_name', 'Computer Science')->first()->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Jane Adviser',
                'email' => 'adviser@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['adviser']->id,
                'department_id' => $depts->where('department_name', 'Engineering')->first()->id,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Bob Student',
                'email' => 'student@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['student']->id,
                'department_id' => $depts->where('department_name', 'Computer Science')->first()->id,
                'year_level' => '3rd Year',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Alice Student',
                'email' => 'student2@example.com',
                'password' => Hash::make('password'),
                'role_id' => $roles['student']->id,
                'department_id' => $depts->where('department_name', 'Engineering')->first()->id,
                'year_level' => '4th Year',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Update department dean_id
        $csDept = Department::where('department_name', 'Computer Science')->first();
        $dean = User::where('email', 'dean@example.com')->first();
        if ($csDept && $dean) {
            $csDept->update(['dean_id' => $dean->id]);
        }

        // Create additional categories
        $categories = [
            ['name' => 'Laboratory Equipment', 'description' => 'Scientific and technical laboratory equipment'],
            ['name' => 'Books & References', 'description' => 'Educational books and reference materials'],
            ['name' => 'Software Licenses', 'description' => 'Software licenses and digital tools'],
            ['name' => 'Cleaning Supplies', 'description' => 'Maintenance and cleaning materials'],
        ];

        foreach ($categories as $catData) {
            Category::firstOrCreate(
                ['name' => $catData['name']],
                $catData
            );
        }

        // Create suppliers
        $suppliers = [
            [
                'name' => 'TechWorld Supplies',
                'contact_person' => 'Mark Johnson',
                'email' => 'mark@techworld.com',
                'phone1' => '02-123-4567',
                'address1' => '123 Tech Street, Manila',
            ],
            [
                'name' => 'Office Plus Inc.',
                'contact_person' => 'Sarah Wilson',
                'email' => 'sarah@officeplus.com',
                'phone1' => '02-234-5678',
                'address1' => '456 Business Ave, Quezon City',
            ],
            [
                'name' => 'EduBooks Publishing',
                'contact_person' => 'David Brown',
                'email' => 'david@edubooks.com',
                'phone1' => '02-345-6789',
                'address1' => '789 Education Blvd, Makati',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(
                ['email' => $supplierData['email']],
                $supplierData
            );
        }

        // Create additional supplies with variants
        $supplies = [
            [
                'name' => 'USB Flash Drive',
                'description' => 'Portable USB storage device',
                'quantity' => 0,
                'unit' => 'pieces',
                'minimum_stock_level' => 5,
                'supply_type' => 'grantable',
                'status' => 'active',
                'has_variants' => true,
                'categories' => ['Electronics'],
                'variants' => [
                    ['name' => '16GB', 'quantity' => 15, 'additional_info' => 'SanDisk Cruzer'],
                    ['name' => '32GB', 'quantity' => 10, 'additional_info' => 'Kingston DataTraveler'],
                    ['name' => '64GB', 'quantity' => 5, 'additional_info' => 'Samsung Bar Plus'],
                ]
            ],
            [
                'name' => 'Programming Books',
                'description' => 'Educational programming textbooks',
                'quantity' => 0,
                'unit' => 'books',
                'minimum_stock_level' => 3,
                'supply_type' => 'borrowable',
                'status' => 'active',
                'has_variants' => true,
                'categories' => ['Books & References'],
                'variants' => [
                    ['name' => 'Java Programming', 'quantity' => 8, 'additional_info' => '9th Edition'],
                    ['name' => 'Python Crash Course', 'quantity' => 6, 'additional_info' => '2nd Edition'],
                    ['name' => 'Web Development', 'quantity' => 4, 'additional_info' => 'HTML, CSS, JS'],
                ]
            ],
            [
                'name' => 'Laboratory Microscope',
                'description' => 'Digital microscope for laboratory use',
                'quantity' => 3,
                'unit' => 'units',
                'minimum_stock_level' => 1,
                'supply_type' => 'borrowable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Laboratory Equipment'],
            ],
            [
                'name' => 'Whiteboard Markers',
                'description' => 'Dry erase markers for whiteboards',
                'quantity' => 24,
                'unit' => 'pieces',
                'minimum_stock_level' => 12,
                'supply_type' => 'consumable',
                'status' => 'active',
                'has_variants' => false,
                'categories' => ['Office Supplies'],
            ],
        ];

        foreach ($supplies as $supplyData) {
            $categoryNames = $supplyData['categories'];
            $variants = $supplyData['variants'] ?? [];
            unset($supplyData['categories'], $supplyData['variants']);

            $supply = Supply::firstOrCreate(
                ['name' => $supplyData['name']],
                $supplyData
            );

            // Attach categories
            $categoryIds = Category::whereIn('name', $categoryNames)->pluck('id');
            $supply->categories()->sync($categoryIds);

            // Create variants if any
            foreach ($variants as $variantData) {
                SupplyVariant::firstOrCreate(
                    [
                        'supply_id' => $supply->id,
                        'variant_name' => $variantData['name']
                    ],
                    [
                        'quantity' => $variantData['quantity'],
                        'attributes' => json_encode(['info' => $variantData['additional_info']])
                    ]
                );
            }

            // Attach suppliers
            $supplier = Supplier::inRandomOrder()->first();
            if ($supplier) {
                $supply->suppliers()->syncWithoutDetaching([$supplier->id]);
            }
        }

        // Create supply requests
        $this->createSupplyRequests();

        // Create borrowed items
        $this->createBorrowedItems();

        echo "Comprehensive test data seeding completed!\n";
    }

    private function createSupplyRequests()
    {
        $users = User::whereIn('role_id', [3, 4])->get(); // Students and advisers
        $supplies = Supply::all();
        $departments = Department::all();

        foreach ($users->take(10) as $user) {
            for ($i = 0; $i < 3; $i++) {
                $supply = $supplies->random();
                $department = $departments->random();
                
                SupplyRequest::create([
                    'user_id' => $user->id,
                    'supply_id' => $supply->id,
                    'item_name' => $supply->name,
                    'quantity' => rand(1, 10),
                    'unit' => 'pcs',
                    'description' => 'For academic project #' . ($i + 1),
                    'department_id' => $department->id,
                    'status' => collect(['pending', 'approved', 'rejected'])->random(),
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(0, 5)),
                ]);
            }
        }
    }

    private function createBorrowedItems()
    {
        $users = User::whereIn('role_id', [3, 4])->get(); // Students and advisers
        $supplies = Supply::all();
        $departments = Department::all();

        foreach ($users->take(8) as $user) {
            $supply = $supplies->random();
            $department = $departments->random();
            
            BorrowedItem::create([
                'user_id' => $user->id,
                'supply_id' => $supply->id,
                'department_id' => $department->id,
                'quantity' => rand(1, 3),
                'borrowed_at' => now()->subDays(rand(1, 14)),
                'returned_at' => rand(0, 1) ? now()->subDays(rand(0, 7)) : null,
            ]);
        }
    }

    private function createIssuedItems()
    {
        $users = User::whereIn('email', ['student@example.com', 'student2@example.com'])->get();
        $grantableSupplies = Supply::where('supply_type', 'grantable')->get();

        foreach ($users as $user) {
            $supply = $grantableSupplies->random();
            IssuedItem::create([
                'user_id' => $user->id,
                'supply_id' => $supply->id,
                'quantity_issued' => rand(1, 3),
                'issued_at' => now()->subDays(rand(1, 10)),
                'purpose' => 'Academic requirement',
                'issued_by' => User::where('email', 'testadmin@example.com')->first()->id ?? 1,
            ]);
        }
    }

    private function createRestockRequests()
    {
        $supplies = Supply::where('quantity', '<', 10)->take(3)->get();
        $suppliers = Supplier::all();

        foreach ($supplies as $supply) {
            RestockRequest::create([
                'supply_id' => $supply->id,
                'supplier_id' => $suppliers->random()->id,
                'quantity_requested' => rand(20, 50),
                'unit_cost' => rand(50, 500),
                'total_cost' => rand(1000, 25000),
                'justification' => 'Stock level below minimum threshold',
                'status' => ['pending', 'approved', 'ordered'][rand(0, 2)],
                'requested_by' => User::where('email', 'testadmin@example.com')->first()->id ?? 1,
                'created_at' => now()->subDays(rand(1, 20)),
            ]);
        }
    }

    private function createLoanRequests()
    {
        $users = User::whereIn('email', ['student@example.com', 'student2@example.com'])->get();
        $borrowableSupplies = Supply::where('supply_type', 'borrowable')->get();

        foreach ($users as $user) {
            $supply = $borrowableSupplies->random();
            LoanRequest::create([
                'user_id' => $user->id,
                'supply_id' => $supply->id,
                'quantity_requested' => 1,
                'purpose' => 'Thesis research',
                'expected_return_date' => now()->addDays(rand(7, 30)),
                'status' => ['pending', 'approved', 'declined'][rand(0, 2)],
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
        }
    }

    private function createManualReceipts()
    {
        $supplies = Supply::take(3)->get();

        foreach ($supplies as $supply) {
            ManualReceipt::create([
                'supply_id' => $supply->id,
                'quantity_received' => rand(10, 30),
                'unit_cost' => rand(25, 200),
                'total_cost' => rand(250, 6000),
                'supplier_name' => 'Manual Supplier ' . rand(1, 5),
                'receipt_date' => now()->subDays(rand(1, 30)),
                'notes' => 'Emergency purchase',
                'recorded_by' => User::where('email', 'testadmin@example.com')->first()->id ?? 1,
                'created_at' => now()->subDays(rand(1, 25)),
            ]);
        }
    }

    private function createInterDepartmentLoanRequests()
    {
        $departments = Department::take(2)->get();
        $supplies = Supply::where('supply_type', 'borrowable')->take(2)->get();

        if ($departments->count() >= 2 && $supplies->count() >= 1) {
            InterDepartmentLoanRequest::create([
                'requesting_department_id' => $departments[0]->id,
                'lending_department_id' => $departments[1]->id,
                'supply_id' => $supplies->first()->id,
                'quantity_requested' => 1,
                'purpose' => 'Inter-department collaboration project',
                'expected_return_date' => now()->addDays(14),
                'status' => 'pending',
                'requested_by' => User::where('email', 'dean@example.com')->first()->id ?? 1,
                'created_at' => now()->subDays(5),
            ]);
        }
    }

    private function createProfileUpdateRequests()
    {
        $user = User::where('email', 'student@example.com')->first();
        if ($user) {
            ProfileUpdateRequest::create([
                'user_id' => $user->id,
                'requested_changes' => json_encode([
                    'contact_number' => '09987654321',
                    'address' => 'New Address 123'
                ]),
                'reason' => 'Updated contact information',
                'status' => 'pending',
                'created_at' => now()->subDays(3),
            ]);
        }
    }

    private function createStatusChangeRequests()
    {
        $supply = Supply::where('status', 'active')->first();
        if ($supply) {
            StatusChangeRequest::create([
                'supply_id' => $supply->id,
                'current_status' => $supply->status,
                'requested_status' => 'maintenance',
                'reason' => 'Requires routine maintenance',
                'status' => 'pending',
                'requested_by' => User::where('email', 'testadmin@example.com')->first()->id ?? 1,
                'created_at' => now()->subDays(2),
            ]);
        }
    }
}