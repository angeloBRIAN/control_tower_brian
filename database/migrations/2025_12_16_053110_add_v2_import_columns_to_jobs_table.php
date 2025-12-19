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
            $table->renameColumn('unit', 'unit_type');
            
            // New Columns
            $table->time('check_in_time')->nullable()->after('job_date');
            $table->string('payment_type')->nullable()->after('job_type'); // CASH, ISP, W, etc
            $table->text('job_description')->nullable()->after('description'); // OPERATION
            $table->date('deadline')->nullable()->after('promise_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->renameColumn('unit_type', 'unit');
            $table->dropColumn(['check_in_time', 'payment_type', 'job_description', 'deadline']);
        });
    }
};
