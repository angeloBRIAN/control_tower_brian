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
            // Invoice-specific fields
            $table->date('invoice_date')->nullable()->after('invoice_number');
            $table->string('type_sale')->nullable()->after('invoice_date');
            $table->decimal('inv_amount', 15, 2)->nullable()->after('type_sale');
            $table->decimal('inv_ppn', 15, 2)->nullable()->after('inv_amount');
            $table->decimal('inv_ppn_meterai', 15, 2)->nullable()->after('inv_ppn');
            
            // Vehicle identification
            $table->string('chassis_number')->nullable()->after('plate_number');
            
            // Date tracking
            $table->date('date_in')->nullable()->after('job_date');
            $table->date('date_out')->nullable()->after('date_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_date',
                'type_sale',
                'inv_amount',
                'inv_ppn',
                'inv_ppn_meterai',
                'chassis_number',
                'date_in',
                'date_out',
            ]);
        });
    }
};
