<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Removes unused columns from part_orders table after workflow redesign.
     * The new Part Tracking workflow no longer uses part_name, part_number, or quantity
     * as we track RQs (requisitions) rather than individual parts.
     */
    public function up(): void
    {
        Schema::table('part_orders', function (Blueprint $table) {
            $table->dropColumn(['part_name', 'part_number', 'quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_orders', function (Blueprint $table) {
            $table->string('part_name')->nullable()->after('job_id');
            $table->string('part_number')->nullable()->after('part_name');
            $table->integer('quantity')->default(1)->after('part_number');
        });
    }
};
