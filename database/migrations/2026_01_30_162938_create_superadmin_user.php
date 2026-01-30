<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create super admin account
        DB::table('users')->updateOrInsert(
            ['email' => 'superadmin@kasirku.com'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@kasirku.com',
                'password' => Hash::make('superadmin123'),
                'role' => 'superadmin',
                'permissions' => json_encode([]),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete super admin account
        DB::table('users')->where('email', 'superadmin@kasirku.com')->delete();
    }
};
