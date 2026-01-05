<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use Illuminate\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        // Remove old English work statuses if they exist
        DropdownOption::where('type', 'work_status')
            ->whereIn('value', ['pending', 'in_progress', 'waiting_parts', 'waiting_approval', 'completed'])
            ->delete();
        
        $options = [
            // Work Status - Indonesian Workflow
            ['type' => 'work_status', 'value' => 'belum_diproses', 'label' => 'Belum Diproses', 'icon' => 'inbox', 'color' => 'secondary', 'sort_order' => 0],
            ['type' => 'work_status', 'value' => 'keluhan_awal', 'label' => 'Keluhan Awal', 'icon' => 'x-circle', 'color' => 'danger', 'sort_order' => 1],
            ['type' => 'work_status', 'value' => 'estimasi', 'label' => 'Estimasi', 'icon' => 'clock', 'color' => 'warning', 'sort_order' => 2],
            ['type' => 'work_status', 'value' => 'acc_customer', 'label' => 'Acc Customer', 'icon' => 'hand-thumbs-up', 'color' => 'info', 'sort_order' => 3],
            ['type' => 'work_status', 'value' => 'pengerjaan', 'label' => 'Pengerjaan', 'icon' => 'play-circle', 'color' => 'primary', 'sort_order' => 4],
            ['type' => 'work_status', 'value' => 'order_parts', 'label' => 'Order Parts', 'icon' => 'gear', 'color' => 'secondary', 'sort_order' => 5],
            ['type' => 'work_status', 'value' => 'pemberkasan', 'label' => 'Pemberkasan', 'icon' => 'file-text', 'color' => 'info', 'sort_order' => 6],
            ['type' => 'work_status', 'value' => 'penjadwalan', 'label' => 'Penjadwalan', 'icon' => 'calendar', 'color' => 'primary', 'sort_order' => 7],
            ['type' => 'work_status', 'value' => 'penjadwalan_campaign', 'label' => 'Penjadwalan Campaign', 'icon' => 'calendar-event', 'color' => 'primary', 'sort_order' => 8],
            ['type' => 'work_status', 'value' => 'proses_warranty', 'label' => 'Proses Warranty', 'icon' => 'shield-check', 'color' => 'warning', 'sort_order' => 9],
            ['type' => 'work_status', 'value' => 'proses_close', 'label' => 'Proses Close', 'icon' => 'box-arrow-right', 'color' => 'secondary', 'sort_order' => 10],
            ['type' => 'work_status', 'value' => 'menunggu_pembayaran', 'label' => 'Menunggu Pembayaran', 'icon' => 'credit-card', 'color' => 'warning', 'sort_order' => 11],
            ['type' => 'work_status', 'value' => 'proses_invoice', 'label' => 'Proses Invoice', 'icon' => 'receipt', 'color' => 'success', 'sort_order' => 12],

            // Payment Type
            ['type' => 'payment_type', 'value' => 'cash', 'label' => 'Cash', 'icon' => 'cash', 'color' => 'success', 'sort_order' => 1],
            ['type' => 'payment_type', 'value' => 'credit', 'label' => 'Credit', 'icon' => 'credit-card', 'color' => 'primary', 'sort_order' => 2],
            ['type' => 'payment_type', 'value' => 'transfer', 'label' => 'Transfer', 'icon' => 'bank', 'color' => 'info', 'sort_order' => 3],
            ['type' => 'payment_type', 'value' => 'warranty', 'label' => 'Warranty', 'icon' => 'shield-check', 'color' => 'warning', 'sort_order' => 4],
            ['type' => 'payment_type', 'value' => 'internal', 'label' => 'Internal', 'icon' => 'building', 'color' => 'secondary', 'sort_order' => 5],

            // Block/Bay
            ['type' => 'block', 'value' => 'A', 'label' => 'Block A', 'icon' => 'grid', 'color' => 'primary', 'sort_order' => 1],
            ['type' => 'block', 'value' => 'B', 'label' => 'Block B', 'icon' => 'grid', 'color' => 'success', 'sort_order' => 2],
            ['type' => 'block', 'value' => 'C', 'label' => 'Block C', 'icon' => 'grid', 'color' => 'warning', 'sort_order' => 3],
            ['type' => 'block', 'value' => 'D', 'label' => 'Block D', 'icon' => 'grid', 'color' => 'info', 'sort_order' => 4],
            ['type' => 'block', 'value' => 'BP', 'label' => 'Body Paint', 'icon' => 'palette', 'color' => 'danger', 'sort_order' => 5],
        ];

        foreach ($options as $option) {
            DropdownOption::updateOrCreate(
                ['type' => $option['type'], 'value' => $option['value']],
                $option
            );
        }
    }
}
