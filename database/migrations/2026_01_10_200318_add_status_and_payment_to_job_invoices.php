<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('job_invoices', function (Blueprint $table) {
            // Invoice payment tracking
            $table->enum('status', ['draft', 'pending', 'partially_paid', 'paid', 'cancelled'])
                  ->default('draft')
                  ->after('invoice_type');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('inv_ppn_meterai');
            $table->timestamp('paid_at')->nullable()->after('paid_amount');
            $table->text('finance_remark')->nullable()->after('notes');
            
            // Index for Kanban queries
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'paid_amount', 'paid_at', 'finance_remark']);
        });
    }
};
