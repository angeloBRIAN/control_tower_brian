<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use Illuminate\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        $options = [
            // Work Status
            ['type' => 'work_status', 'value' => 'pending', 'label' => 'Pending', 'icon' => 'hourglass-split', 'color' => 'secondary', 'sort_order' => 1],
            ['type' => 'work_status', 'value' => 'in_progress', 'label' => 'In Progress', 'icon' => 'play-circle', 'color' => 'primary', 'sort_order' => 2],
            ['type' => 'work_status', 'value' => 'waiting_parts', 'label' => 'Waiting Parts', 'icon' => 'gear', 'color' => 'warning', 'sort_order' => 3],
            ['type' => 'work_status', 'value' => 'waiting_approval', 'label' => 'Waiting Approval', 'icon' => 'hand-thumbs-up', 'color' => 'info', 'sort_order' => 4],
            ['type' => 'work_status', 'value' => 'completed', 'label' => 'Completed', 'icon' => 'check2-circle', 'color' => 'success', 'sort_order' => 5],

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
