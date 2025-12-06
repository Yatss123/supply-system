<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

echo "Creating Test Admin User...\n";
echo "================================\n";

$adminEmail = 'test@email.com';
$password = 'passowrd'; // using provided password spelling

// Ensure the admin role exists
$adminRole = Role::where('name', 'admin')->first();
if (!$adminRole) {
    echo "Admin role not found. Creating role...\n";
    $adminRole = Role::create([
        'name' => 'admin',
        'description' => 'Administrator with management privileges'
    ]);
}

// Use an existing department if available (optional)
$department = Department::first();
$departmentId = $department ? $department->id : null;
if ($departmentId) {
    echo "Using Department ID: {$departmentId}\n";
} else {
    echo "No departments found. Proceeding without department.\n";
}

// Create or update the user
$user = User::where('email', $adminEmail)->first();
if ($user) {
    echo "User already exists. Updating role and password...\n";
} else {
    echo "Creating new admin user...\n";
    $user = new User();
}

$user->fill([
    'name' => 'Test Admin',
    'email' => $adminEmail,
    'password' => Hash::make($password),
    'role_id' => $adminRole->id,
    'department_id' => $departmentId,
    'email_verified_at' => now(),
    'profile_completed' => true,
]);

$user->save();

echo "\n✅ Test admin user ready!\n";
echo "   Email: {$adminEmail}\n";
echo "   Password: {$password}\n";
echo "   Role: admin\n";
echo "   User ID: {$user->id}\n";
echo "\nYou can now log in and access /users.\n";