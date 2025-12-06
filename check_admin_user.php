<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;

echo "=== Checking Admin User Details ===\n\n";

// Check all admin-related users
$adminUsers = User::whereHas('role', function($query) {
    $query->whereIn('name', ['admin', 'super_admin', 'dean', 'adviser']);
})->with('role')->get();

echo "Admin Users Found:\n";
foreach ($adminUsers as $user) {
    echo "- Email: {$user->email}\n";
    echo "  Role: {$user->role->name}\n";
    echo "  Profile Completed: " . ($user->profile_completed ? 'Yes' : 'No') . "\n";
    echo "  Has Admin Privileges: " . ($user->hasAdminPrivileges() ? 'Yes' : 'No') . "\n";
    echo "  Address: " . ($user->address ?? 'Not set') . "\n";
    echo "  Date of Birth: " . ($user->date_of_birth ?? 'Not set') . "\n";
    echo "  Civil Status: " . ($user->civil_status ?? 'Not set') . "\n";
    echo "  Gender: " . ($user->gender ?? 'Not set') . "\n";
    echo "  Contact Number: " . ($user->contact_number ?? 'Not set') . "\n";
    echo "  ---\n";
}

// Check the specific incomplete admin we created
$incompleteAdmin = User::where('email', 'incompleteadmin@example.com')->with('role')->first();
if ($incompleteAdmin) {
    echo "\nIncomplete Admin User:\n";
    echo "- Email: {$incompleteAdmin->email}\n";
    echo "  Role: {$incompleteAdmin->role->name}\n";
    echo "  Profile Completed: " . ($incompleteAdmin->profile_completed ? 'Yes' : 'No') . "\n";
    echo "  Has Admin Privileges: " . ($incompleteAdmin->hasAdminPrivileges() ? 'Yes' : 'No') . "\n";
    echo "  Address: " . ($incompleteAdmin->address ?? 'Not set') . "\n";
    echo "  Date of Birth: " . ($incompleteAdmin->date_of_birth ?? 'Not set') . "\n";
    echo "  Civil Status: " . ($incompleteAdmin->civil_status ?? 'Not set') . "\n";
    echo "  Gender: " . ($incompleteAdmin->gender ?? 'Not set') . "\n";
    echo "  Contact Number: " . ($incompleteAdmin->contact_number ?? 'Not set') . "\n";
}

echo "\n=== Available Roles ===\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\nDone!\n";