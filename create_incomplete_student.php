<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Department;

echo "Creating Student User with Incomplete Profile...\n";
echo "===============================================\n";

// Get student role
$studentRole = Role::where('name', 'student')->first();
if (!$studentRole) {
    echo "❌ Student role not found. Please run RoleSeeder first.\n";
    exit(1);
}

// Get a department
$department = Department::first();
if (!$department) {
    echo "❌ No departments found. Please ensure departments exist.\n";
    exit(1);
}

// Check if student already exists
$existingStudent = User::where('email', 'incompletestudent@example.com')->first();
if ($existingStudent) {
    echo "⚠️  Student user already exists. Updating...\n";
    $student = $existingStudent;
} else {
    echo "Creating new student user...\n";
    $student = new User();
}

// Create student with incomplete profile
$student->fill([
    'name' => 'Incomplete Student',
    'email' => 'incompletestudent@example.com',
    'password' => bcrypt('password'),
    'role_id' => $studentRole->id,
    'department_id' => $department->id,
    'year_level' => '1st Year',
    'profile_completed' => false,
    // Intentionally leaving these fields empty to test enforcement
    'address' => null,
    'date_of_birth' => null,
    'civil_status' => null,
    'gender' => null,
    'contact_number' => null,
]);

$student->save();

echo "✅ Student user created successfully!\n";
echo "   Email: incompletestudent@example.com\n";
echo "   Password: password\n";
echo "   Role: {$studentRole->name}\n";
echo "   Year Level: {$student->year_level}\n";
echo "   Profile Complete: " . ($student->profile_completed ? 'Yes' : 'No') . "\n";
echo "   Address: " . ($student->address ?: 'Not set') . "\n";
echo "   Date of Birth: " . ($student->date_of_birth ?: 'Not set') . "\n";
echo "   Civil Status: " . ($student->civil_status ?: 'Not set') . "\n";
echo "   Gender: " . ($student->gender ?: 'Not set') . "\n";
echo "   Contact Number: " . ($student->contact_number ?: 'Not set') . "\n";

echo "\nThis student should be required to complete profile before accessing features.\n";