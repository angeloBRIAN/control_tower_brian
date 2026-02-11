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
            $table->boolean('is_stale')->default(false)->after('work_status');
        });

        // Migrate existing "needs_attention" jobs
        // Set is_stale = true and reset status to default
        \DB::table('jobs')
            ->where('work_status', 'needs_attention')
            ->update([
                'is_stale' => true,
                'work_status' => '1. Belum diproses (Tunggu Antrian)'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn('is_stale');
        });
    }
};
