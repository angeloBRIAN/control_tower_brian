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
        Schema::table('jobs', function (Blueprint $table) {
            // Customer info
            $table->string('customer_name')->nullable()->after('plate_number');
            $table->text('customer_address')->nullable()->after('customer_name');
            
            // Personnel
            $table->string('foreman')->nullable()->after('technician');
            
            // Job identifiers
            $table->string('job_card')->nullable()->after('job_number');
            
            // Vehicle info
            $table->string('unit')->nullable()->after('plate_number'); // Model/unit
            $table->string('type_unit')->nullable()->after('unit');
            $table->string('account_no')->nullable()->after('type_unit');
            $table->date('date_first_reg')->nullable()->after('account_no');
            
            // Sales breakdown
            $table->decimal('labour_sales', 15, 2)->nullable()->after('estimated_amount');
            $table->decimal('part_sales', 15, 2)->nullable()->after('labour_sales');
            $table->decimal('total_sales', 15, 2)->nullable()->after('part_sales');
            
            // Order/Parts tracking
            $table->string('rq')->nullable()->after('total_sales'); // Requisition
            $table->string('no_order_part_mbina')->nullable()->after('rq');
            $table->text('lain_lain')->nullable()->after('no_order_part_mbina'); // Other/misc
            
            // Remarks tracking
            $table->text('update_remarks')->nullable()->after('latest_remark');
            $table->timestamp('update_at')->nullable()->after('update_remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'customer_name',
                'customer_address',
                'foreman',
                'job_card',
                'unit',
                'type_unit',
                'account_no',
                'date_first_reg',
                'labour_sales',
                'part_sales',
                'total_sales',
                'rq',
                'no_order_part_mbina',
                'lain_lain',
                'update_remarks',
                'update_at',
            ]);
        });
    }
};
