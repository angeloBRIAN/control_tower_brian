<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrate old part order statuses to new 6-status workflow
     */
    public function up(): void
    {
        // Map old statuses to new statuses
        $statusMappings = [
            'buka_rq' => 'rq_sent',
            'ordered' => 'ordering',
            'confirmed' => 'ready',
            'shipped' => 'ready',
            'installed' => 'received',
        ];

        foreach ($statusMappings as $oldStatus => $newStatus) {
            DB::table('part_orders')
                ->where('status', $oldStatus)
                ->update(['status' => $newStatus]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map new statuses back to old statuses
        $statusMappings = [
            'rq_sent' => 'buka_rq',
            'processing' => 'buka_rq', // No direct equivalent, map to buka_rq
            'ordering' => 'ordered',
            'ready' => 'confirmed',
        ];

        foreach ($statusMappings as $newStatus => $oldStatus) {
            DB::table('part_orders')
                ->where('status', $newStatus)
                ->update(['status' => $oldStatus]);
        }
    }
};
