<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration to update all legacy work_status values to the new format.
 * This ensures consistent data and eliminates the need for runtime normalization.
 */
return new class extends Migration
{
    /**
     * Legacy status to new status mapping
     */
    protected $statusMap = [
        'belum_diproses' => '1. Belum diproses (Tunggu Antrian)',
        'pending' => '1. Belum diproses (Tunggu Antrian)',
        'keluhan_awal' => '2. Pengerjaan Diagnosa Awal',
        'diagnosa' => '2. Pengerjaan Diagnosa Awal',
        'estimasi' => '3. Estimasi (Proses Warranty -> Tips case, Eskulab, Xsp)',
        'acc_customer' => '4. Acc Customer/Warranty',
        'order_parts' => '5. Buka RQ (Qrder Parts)',
        'parts_received' => '6. Parts Datang (Parts Received)',
        'penjadwalan' => '7. Penjadwalan (Unit dibawa customer)',
        'pengerjaan' => '8. Pengerjaan',
        'in_progress' => '8. Pengerjaan',
        'pemberkasan' => '9. Pemberkasan (Body Paint/Cash/Warranty)',
        'proses_close' => '10. Proses Close Job (Pengerjaan selesai)',
        'close_job' => '10. Proses Close Job (Pengerjaan selesai)',
        'proses_invoice' => '11. Proses Invoice',
        'menunggu_pembayaran' => '12. Menunggu Pembayaran',
        'sudah_dibayar' => '13. Sudah Dibayar',
        'completed' => '13. Sudah Dibayar',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->statusMap as $legacy => $new) {
            $updated = DB::table('jobs')
                ->where('work_status', $legacy)
                ->update(['work_status' => $new]);
            
            if ($updated > 0) {
                echo "Updated {$updated} jobs from '{$legacy}' to '{$new}'\n";
            }
        }
        
        // Also update any NULL work_status to the first status
        $nullUpdated = DB::table('jobs')
            ->whereNull('work_status')
            ->update(['work_status' => '1. Belum diproses (Tunggu Antrian)']);
        
        if ($nullUpdated > 0) {
            echo "Updated {$nullUpdated} jobs with NULL work_status\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse mapping (new format to legacy)
        $reverseMap = [
            '1. Belum diproses (Tunggu Antrian)' => 'belum_diproses',
            '2. Pengerjaan Diagnosa Awal' => 'diagnosa',
            '3. Estimasi (Proses Warranty -> Tips case, Eskulab, Xsp)' => 'estimasi',
            '4. Acc Customer/Warranty' => 'acc_customer',
            '5. Buka RQ (Qrder Parts)' => 'order_parts',
            '6. Parts Datang (Parts Received)' => 'parts_received',
            '7. Penjadwalan (Unit dibawa customer)' => 'penjadwalan',
            '8. Pengerjaan' => 'pengerjaan',
            '9. Pemberkasan (Body Paint/Cash/Warranty)' => 'pemberkasan',
            '10. Proses Close Job (Pengerjaan selesai)' => 'proses_close',
            '11. Proses Invoice' => 'proses_invoice',
            '12. Menunggu Pembayaran' => 'menunggu_pembayaran',
            '13. Sudah Dibayar' => 'sudah_dibayar',
        ];
        
        foreach ($reverseMap as $new => $legacy) {
            DB::table('jobs')
                ->where('work_status', $new)
                ->update(['work_status' => $legacy]);
        }
    }
};
