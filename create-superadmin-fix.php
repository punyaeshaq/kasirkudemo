<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

echo "Starting Super Admin Creation...\n";

try {
    // 1. Check if user exists
    $exists = DB::table('users')->where('email', 'superadmin@kasirku.com')->exists();

    if ($exists) {
        echo "User already exists. Updating password...\n";
        DB::table('users')->where('email', 'superadmin@kasirku.com')->update([
            'password' => Hash::make('superadmin123'),
            'role' => 'superadmin',
            'is_active' => 1
        ]);
        echo "SUCCESS: Password reset for existing user.\n";
    } else {
        echo "User not found. Creating new user...\n";

        $id = DB::table('users')->insertGetId([
            'name' => 'Super Admin',
            'email' => 'superadmin@kasirku.com',
            'password' => Hash::make('superadmin123'),
            'role' => 'superadmin',
            'permissions' => json_encode([]), // Use empty JSON array
            'is_active' => 1,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        echo "SUCCESS: Created user with ID: $id\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "Done.\n";
