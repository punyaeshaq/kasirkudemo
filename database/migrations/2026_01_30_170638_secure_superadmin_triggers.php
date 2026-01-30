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
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
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
        } elseif ($driver === 'pgsql') {
            // Postgres Implementation

            // Function for delete trigger
            DB::unprepared("
                CREATE OR REPLACE FUNCTION prevent_superadmin_delete_func() RETURNS TRIGGER AS $$
                BEGIN
                    IF OLD.role = 'superadmin' THEN
                        RAISE EXCEPTION 'SECURITY ALERT: Cannot delete Super Admin account!';
                    END IF;
                    RETURN OLD;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // Trigger for delete
            DB::unprepared("
                CREATE TRIGGER prevent_superadmin_delete
                BEFORE DELETE ON users
                FOR EACH ROW
                EXECUTE FUNCTION prevent_superadmin_delete_func();
            ");

            // Function for update trigger
            DB::unprepared("
                CREATE OR REPLACE FUNCTION prevent_superadmin_update_func() RETURNS TRIGGER AS $$
                BEGIN
                    IF OLD.role = 'superadmin' THEN
                        -- Block if role is changed
                        IF NEW.role != 'superadmin' THEN
                            RAISE EXCEPTION 'SECURITY ALERT: Cannot demote Super Admin!';
                        END IF;
                        
                        -- Block if email is changed
                        IF NEW.email != OLD.email THEN
                            RAISE EXCEPTION 'SECURITY ALERT: Cannot change Super Admin email!';
                        END IF;
                    END IF;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");

            // Trigger for update
            DB::unprepared("
                CREATE TRIGGER prevent_superadmin_update
                BEFORE UPDATE ON users
                FOR EACH ROW
                EXECUTE FUNCTION prevent_superadmin_update_func();
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_delete");
            DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_update");
        } elseif ($driver === 'pgsql') {
            DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_delete ON users");
            DB::unprepared("DROP FUNCTION IF EXISTS prevent_superadmin_delete_func()");

            DB::unprepared("DROP TRIGGER IF EXISTS prevent_superadmin_update ON users");
            DB::unprepared("DROP FUNCTION IF EXISTS prevent_superadmin_update_func()");
        }
    }
};
