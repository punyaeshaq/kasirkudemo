<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

try {
    $user = DB::table('users')->where('email', 'superadmin@kasirku.com')->first();
    if ($user) {
        echo "User Found:\n";
        echo "ID: " . $user->id . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Role: " . $user->role . "\n";
        echo "Is Active: " . $user->is_active . "\n";
        echo "Password Hash: " . substr($user->password, 0, 10) . "...\n";
    } else {
        echo "User 'superadmin@kasirku.com' NOT FOUND.\n";
        // List all users to see what's there
        $users = DB::table('users')->select('id', 'email', 'role')->get();
        echo "Existing Users:\n";
        foreach ($users as $u) {
            echo "- ID: {$u->id}, Email: {$u->email}, Role: {$u->role}\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
