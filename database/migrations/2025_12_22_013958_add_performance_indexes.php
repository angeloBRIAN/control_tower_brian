<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adding indexes to frequently queried columns for better performance.
     * Uses raw SQL to avoid duplicate index errors.
     */
    public function up(): void
    {
        // Jobs table indexes
        $this->addIndexIfNotExists('jobs', 'jobs_customer_name_index', 'customer_name');
        $this->addIndexIfNotExists('jobs', 'jobs_job_date_index', 'job_date');
        $this->addIndexIfNotExists('jobs', 'jobs_service_advisor_index', 'service_advisor');
        $this->addIndexIfNotExists('jobs', 'jobs_foreman_index', 'foreman');
        $this->addIndexIfNotExists('jobs', 'jobs_need_part_index', 'need_part');
        $this->addCompositeIndexIfNotExists('jobs', 'jobs_status_job_date_index', ['status', 'job_date']);

        // Vehicles table indexes
        $this->addIndexIfNotExists('vehicles', 'vehicles_customer_name_index', 'customer_name');
        $this->addIndexIfNotExists('vehicles', 'vehicles_is_in_workshop_index', 'is_in_workshop');

        // Bookings table indexes
        $this->addIndexIfNotExists('bookings', 'bookings_booking_date_index', 'booking_date');
        $this->addIndexIfNotExists('bookings', 'bookings_customer_name_index', 'customer_name');
        $this->addIndexIfNotExists('bookings', 'bookings_status_index', 'status');

        // PDI Records table indexes
        $this->addIndexIfNotExists('pdi_records', 'pdi_records_pdi_date_index', 'pdi_date');

        // Towing Records table indexes
        $this->addIndexIfNotExists('towing_records', 'towing_records_scheduled_date_index', 'scheduled_date');
        $this->addIndexIfNotExists('towing_records', 'towing_records_status_index', 'status');

        // Job Invoices table indexes
        $this->addIndexIfNotExists('job_invoices', 'job_invoices_invoice_date_index', 'invoice_date');
        $this->addIndexIfNotExists('job_invoices', 'job_invoices_invoice_number_index', 'invoice_number');

        // Notifications table indexes
        $this->addIndexIfNotExists('notifications', 'notifications_read_at_index', 'read_at');
        $this->addCompositeIndexIfNotExists('notifications', 'notifications_user_id_read_at_index', ['user_id', 'read_at']);

        // Audit Logs table indexes
        $this->addIndexIfNotExists('audit_logs', 'audit_logs_auditable_type_index', 'auditable_type');
        $this->addIndexIfNotExists('audit_logs', 'audit_logs_created_at_index', 'created_at');
        $this->addCompositeIndexIfNotExists('audit_logs', 'audit_logs_auditable_type_auditable_id_index', ['auditable_type', 'auditable_id']);
    }

    /**
     * Add index if it doesn't already exist
     */
    private function addIndexIfNotExists(string $table, string $indexName, string $column): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->index($column);
            });
        }
    }

    /**
     * Add composite index if it doesn't already exist
     */
    private function addCompositeIndexIfNotExists(string $table, string $indexName, array $columns): void
    {
        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($columns) {
                $t->index($columns);
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('jobs', 'jobs_customer_name_index');
        $this->dropIndexIfExists('jobs', 'jobs_job_date_index');
        $this->dropIndexIfExists('jobs', 'jobs_service_advisor_index');
        $this->dropIndexIfExists('jobs', 'jobs_foreman_index');
        $this->dropIndexIfExists('jobs', 'jobs_need_part_index');
        $this->dropIndexIfExists('jobs', 'jobs_status_job_date_index');

        $this->dropIndexIfExists('vehicles', 'vehicles_customer_name_index');
        $this->dropIndexIfExists('vehicles', 'vehicles_is_in_workshop_index');

        $this->dropIndexIfExists('bookings', 'bookings_booking_date_index');
        $this->dropIndexIfExists('bookings', 'bookings_customer_name_index');
        $this->dropIndexIfExists('bookings', 'bookings_status_index');

        $this->dropIndexIfExists('pdi_records', 'pdi_records_pdi_date_index');

        $this->dropIndexIfExists('towing_records', 'towing_records_scheduled_date_index');
        $this->dropIndexIfExists('towing_records', 'towing_records_status_index');

        $this->dropIndexIfExists('job_invoices', 'job_invoices_invoice_date_index');
        $this->dropIndexIfExists('job_invoices', 'job_invoices_invoice_number_index');

        $this->dropIndexIfExists('notifications', 'notifications_read_at_index');
        $this->dropIndexIfExists('notifications', 'notifications_user_id_read_at_index');

        $this->dropIndexIfExists('audit_logs', 'audit_logs_auditable_type_index');
        $this->dropIndexIfExists('audit_logs', 'audit_logs_created_at_index');
        $this->dropIndexIfExists('audit_logs', 'audit_logs_auditable_type_auditable_id_index');
    }

    /**
     * Drop index if it exists
     */
    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $t) use ($indexName) {
                $t->dropIndex($indexName);
            });
        }
    }
};
