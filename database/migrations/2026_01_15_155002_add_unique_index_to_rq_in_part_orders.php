<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add unique index to RQ column (nullable values are allowed to be duplicate)
     */
    public function up(): void
    {
        // Fix duplicate RQs before applying unique index
        // This ensures existing production data won't fail the migration
        $duplicates = Illuminate\Support\Facades\DB::table('part_orders')
            ->select('rq', Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->whereNotNull('rq')
            ->groupBy('rq')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $orders = Illuminate\Support\Facades\DB::table('part_orders')
                ->where('rq', $dup->rq)
                ->orderBy('created_at')
                ->get();

            // Keep the first one (oldest) as is, shift it off
            $orders->shift();

            // Suffix the rest with -A, -B, etc.
            foreach ($orders as $index => $order) {
                $suffix = chr(65 + $index); // A, B, C...
                Illuminate\Support\Facades\DB::table('part_orders')
                    ->where('id', $order->id)
                    ->update(['rq' => $order->rq . '-' . $suffix]);
            }
        }

        Schema::table('part_orders', function (Blueprint $table) {
            // Make RQ unique - nullable values won't conflict
            $table->unique('rq', 'part_orders_rq_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('part_orders', function (Blueprint $table) {
            $table->dropUnique('part_orders_rq_unique');
        });
    }
};
