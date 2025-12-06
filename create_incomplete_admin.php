<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;

echo "Creating Admin User with Incomplete Profile...\n";
echo "==============================================\n";

// Get admin role
$adminRole = Role::where('name', 'admin')->first();
if (!$adminRole) {
    echo "❌ Admin role not found. Please run RoleSeeder first.\n";
    exit(1);
}

// Get a department
$department = Department::first();
if (!$department) {
    echo "❌ No departments found. Please ensure departments exist.\n";
    exit(1);
}

// Check if admin already exists
$existingAdmin = User::where('email', 'incompleteadmin@example.com')->first();
if ($existingAdmin) {
    echo "⚠️  Admin user already exists. Updating...\n";
    $admin = $existingAdmin;
} else {
    echo "Creating new admin user...\n";
    $admin = new User();
}

// Create admin with incomplete profile
$admin->fill([
    'name' => 'Incomplete Admin',
    'email' => 'incompleteadmin@example.com',
    'password' => bcrypt('password'),
    'role_id' => $adminRole->id,
    'department_id' => $department->id,
    'profile_completed' => false,
    // Intentionally leaving these fields empty to test bypass
    'address' => null,
    'date_of_birth' => null,
    'civil_status' => null,
    'gender' => null,
    'contact_number' => null,
]);

$admin->save();

echo "✅ Admin user created successfully!\n";
echo "   Email: incompleteadmin@example.com\n";
echo "   Password: password\n";
echo "   Role: {$adminRole->name}\n";
echo "   Profile Complete: " . ($admin->profile_completed ? 'Yes' : 'No') . "\n";
echo "   Address: " . ($admin->address ?: 'Not set') . "\n";
echo "   Date of Birth: " . ($admin->date_of_birth ?: 'Not set') . "\n";
echo "   Civil Status: " . ($admin->civil_status ?: 'Not set') . "\n";
echo "   Gender: " . ($admin->gender ?: 'Not set') . "\n";
echo "   Contact Number: " . ($admin->contact_number ?: 'Not set') . "\n";

echo "\nThis admin should be able to access all features despite incomplete profile.\n";