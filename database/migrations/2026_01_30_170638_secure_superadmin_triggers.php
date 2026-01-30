<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TRIGGER 1: Prevent Deleting Super Admin
        DB::unprepared("
            CREATE TRIGGER prevent_superadmin_delete BEFORE DELETE ON users
            FOR EACH ROW
            BEGIN
                IF OLD.role = 'superadmin' THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SECURITY ALERT: Cannot delete Super Admin account!';
                END IF;
            END
        ");

        // TRIGGER 2: Prevent Changing Super Admin Role or Email
        DB::unprepared("
            CREATE TRIGGER prevent_superadmin_update BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF OLD.role = 'superadmin' THEN
                    -- Block if role is changed
                    IF NEW.role != 'superadmin' THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SECURITY ALERT: Cannot demote Super Admin!';
                    END IF;
                    
                    -- Block if email is changed
                    IF NEW.email != OLD.email THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'SECURITY ALERT: Cannot change Super Admin email!';
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_delete");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_update");
    }
};
