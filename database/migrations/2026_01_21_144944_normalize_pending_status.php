<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize 'pending' to '1. Belum diproses (Tunggu Antrian)'
        DB::table('jobs')
            ->where('work_status', 'pending')
            ->update(['work_status' => '1. Belum diproses (Tunggu Antrian)']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back if needed (optional, but good practice)
        // Since we can't distinguish which ones were originally 'pending' vs 'belum_diproses' after normalization,
        // we generally don't revert data normalization perfectly unless we tracked it.
        // For this fix, a down method that does nothing is acceptable, or we could revert all to 'belum_diproses' 
        // but that might be destructive. Leaving empty or commenting out is safest.
    }
};
