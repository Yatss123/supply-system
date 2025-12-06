<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Carbon\Carbon;

echo "Completing Test User Profiles...\n";
echo "================================\n";

$users = User::whereIn('email', ['testuser@example.com', 'testadmin@example.com'])->get();

foreach ($users as $user) {
    echo "Updating profile for: " . $user->name . " (" . $user->email . ")\n";
    
    $user->update([
        'profile_completed' => true,
        'address' => '123 Test Street, Test City',
        'date_of_birth' => Carbon::parse('1990-01-01'),
        'civil_status' => 'Single',
        'gender' => 'Male',
        'contact_number' => '09123456789',
        'year_level' => $user->role && $user->role->name === 'student' ? '3rd Year' : null
    ]);
    
    echo "✓ Profile completed for " . $user->name . "\n";
}

echo "\nAll test user profiles have been completed!\n";
echo "Users can now access all features including quick actions.\n";