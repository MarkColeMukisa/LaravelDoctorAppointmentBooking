<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function __construct()
    {
        if (config('database.default') === 'pgsql') {
            $this->withinTransaction = false;
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_patient_status_audits_no_update
                BEFORE UPDATE ON patient_status_audits
                FOR EACH ROW
                BEGIN
                    SELECT RAISE(ABORT, 'Patient status audits are immutable.');
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_patient_status_audits_no_delete
                BEFORE DELETE ON patient_status_audits
                FOR EACH ROW
                BEGIN
                    SELECT RAISE(ABORT, 'Patient status audits cannot be deleted.');
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_patient_status_change_requests_guard_update
                BEFORE UPDATE ON patient_status_change_requests
                FOR EACH ROW
                WHEN OLD.status <> 'pending'
                BEGIN
                    SELECT RAISE(ABORT, 'Finalized status change requests are immutable.');
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_patient_status_change_requests_no_delete
                BEFORE DELETE ON patient_status_change_requests
                FOR EACH ROW
                BEGIN
                    SELECT RAISE(ABORT, 'Status change requests cannot be deleted.');
                END;
            ");

            return;
        }

        if ($driver === 'mysql') {
            DB::unprepared("
                CREATE TRIGGER trg_patient_status_audits_no_update
                BEFORE UPDATE ON patient_status_audits
                FOR EACH ROW
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Patient status audits are immutable.';
            ");

            DB::unprepared("
                CREATE TRIGGER trg_patient_status_audits_no_delete
                BEFORE DELETE ON patient_status_audits
                FOR EACH ROW
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Patient status audits cannot be deleted.';
            ");

            DB::unprepared("
                CREATE TRIGGER trg_patient_status_change_requests_guard_update
                BEFORE UPDATE ON patient_status_change_requests
                FOR EACH ROW
                BEGIN
                    IF OLD.status <> 'pending' THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Finalized status change requests are immutable.';
                    END IF;
                END;
            ");

            DB::unprepared("
                CREATE TRIGGER trg_patient_status_change_requests_no_delete
                BEFORE DELETE ON patient_status_change_requests
                FOR EACH ROW
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Status change requests cannot be deleted.';
            ");

            return;
        }

        if ($driver === 'pgsql') {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION patient_status_audits_immutable_guard()
                RETURNS trigger
                LANGUAGE plpgsql
                AS $$
                BEGIN
                    RAISE EXCEPTION 'Patient status audits are immutable.';
                END;
                $$;
            ");

            DB::unprepared('
                CREATE TRIGGER trg_patient_status_audits_immutable_guard
                BEFORE UPDATE OR DELETE ON patient_status_audits
                FOR EACH ROW
                EXECUTE FUNCTION patient_status_audits_immutable_guard();
            ');

            DB::unprepared("
                CREATE OR REPLACE FUNCTION patient_status_change_requests_immutable_guard()
                RETURNS trigger
                LANGUAGE plpgsql
                AS $$
                BEGIN
                    IF TG_OP = 'DELETE' THEN
                        RAISE EXCEPTION 'Status change requests cannot be deleted.';
                    END IF;

                    IF OLD.status <> 'pending' THEN
                        RAISE EXCEPTION 'Finalized status change requests are immutable.';
                    END IF;

                    RETURN NEW;
                END;
                $$;
            ");

            DB::unprepared('
                CREATE TRIGGER trg_patient_status_change_requests_immutable_guard
                BEFORE UPDATE OR DELETE ON patient_status_change_requests
                FOR EACH ROW
                EXECUTE FUNCTION patient_status_change_requests_immutable_guard();
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_audits_no_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_audits_no_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_change_requests_guard_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_change_requests_no_delete;');

            return;
        }

        if ($driver === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_audits_no_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_audits_no_delete;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_change_requests_guard_update;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_change_requests_no_delete;');

            return;
        }

        if ($driver === 'pgsql') {
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_audits_immutable_guard ON patient_status_audits;');
            DB::unprepared('DROP TRIGGER IF EXISTS trg_patient_status_change_requests_immutable_guard ON patient_status_change_requests;');
            DB::unprepared('DROP FUNCTION IF EXISTS patient_status_audits_immutable_guard();');
            DB::unprepared('DROP FUNCTION IF EXISTS patient_status_change_requests_immutable_guard();');
        }
    }
};
