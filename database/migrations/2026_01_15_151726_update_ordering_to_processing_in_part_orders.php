<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Merge 'ordering' status into 'processing' for 5-status workflow.
     */
    public function up(): void
    {
        // Update all 'ordering' status to 'processing'
        DB::table('part_orders')
            ->where('status', 'ordering')
            ->update(['status' => 'processing']);
    }

    /**
     * Reverse the migrations.
     * Note: Cannot reliably reverse as we don't know which were originally 'ordering'
     */
    public function down(): void
    {
        // No reverse action - data cannot be reliably restored
    }
};
