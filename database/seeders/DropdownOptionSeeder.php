<?php

namespace Database\Seeders;

use App\Models\DropdownOption;
use Illuminate\Database\Seeder;

class DropdownOptionSeeder extends Seeder
{
    public function run(): void
    {
        // NOTE: Work statuses are now hardcoded in Job::WORK_STATUSES constant
        // This provides version control and prevents seeder from breaking automation
        // See: app/Models/Job.php - WORK_STATUSES and WORK_STATUS_META constants
        
        // Clean up any existing work_status entries in database
        DropdownOption::where('type', 'work_status')->delete();
        
        $options = [
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
