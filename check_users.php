<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "Test Users Status:\n";
echo "==================\n";

$users = User::whereIn('email', ['testuser@example.com', 'testadmin@example.com'])
    ->with('role')
    ->get();

foreach ($users as $user) {
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . ($user->role ? $user->role->name : 'No Role') . "\n";
    echo "Profile Complete: " . ($user->profile_completed ? 'Yes' : 'No') . "\n";
    echo "Address: " . ($user->address ?: 'Missing') . "\n";
    echo "Date of Birth: " . ($user->date_of_birth ?: 'Missing') . "\n";
    echo "Civil Status: " . ($user->civil_status ?: 'Missing') . "\n";
    echo "Gender: " . ($user->gender ?: 'Missing') . "\n";
    echo "Contact Number: " . ($user->contact_number ?: 'Missing') . "\n";
    echo "Department ID: " . ($user->department_id ?: 'Missing') . "\n";
    echo "---\n";
}