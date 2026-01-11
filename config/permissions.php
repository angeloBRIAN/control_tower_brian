<?php

/**
 * Role-based Permissions Configuration.
 * 
 * Define which permissions each role has.
 * Used by models and middleware to check access.
 */

return [
    'roles' => [
        'admin' => [
            'manage_users',
            'manage_settings',
            'view_audit_logs',
            'broadcast_announcements',
            'manage_dropdowns',
            'import_data',
            'export_data',
            'view_all_jobs',
            'manage_all_jobs',
        ],
        
        'manager' => [
            'broadcast_announcements',
            'view_all_jobs',
            'view_reports',
            'export_data',
            'manage_team',
        ],
        
        'control_tower' => [
            'view_all_jobs',
            'view_reports',
            'manage_bookings',
            'manage_pdi',
            'manage_towing',
        ],
        
        'service_advisor' => [
            'create_jobs',
            'manage_own_jobs',
            'view_reports',
            'manage_bookings',
        ],
        
        'foreman' => [
            'manage_own_jobs',
            'update_work_status',
            'add_remarks',
        ],
        
        'finance' => [
            'view_invoices',
            'manage_invoices',
            'view_reports',
            'export_data',
        ],
        
        'sparepart' => [
            'manage_parts',
            'view_jobs',
            'update_parts_status',
        ],
    ],
    
    // Permission descriptions for UI
    'descriptions' => [
        'broadcast_announcements' => 'Create and send broadcast announcements to all users',
        'manage_users' => 'Create, edit, and delete user accounts',
        'manage_settings' => 'Modify system settings and configuration',
        'view_audit_logs' => 'View audit logs and activity history',
        'manage_dropdowns' => 'Manage dropdown options and master data',
        'import_data' => 'Import data from Excel/ODS files',
        'export_data' => 'Export reports and data',
        'view_all_jobs' => 'View all jobs in the system',
        'manage_all_jobs' => 'Edit and manage all jobs',
        'view_reports' => 'Access reports and analytics',
        'manage_team' => 'Manage team members and workload',
        'create_jobs' => 'Create new job orders',
        'manage_own_jobs' => 'Manage jobs assigned to self',
        'update_work_status' => 'Update job work status',
        'add_remarks' => 'Add remarks and comments to jobs',
        'manage_bookings' => 'Create and manage service bookings',
        'manage_pdi' => 'Manage pre-delivery inspections',
        'manage_towing' => 'Manage towing records',
        'view_invoices' => 'View invoice information',
        'manage_invoices' => 'Create and manage invoices',
        'manage_parts' => 'Manage parts orders and inventory',
        'view_jobs' => 'View job details',
        'update_parts_status' => 'Update parts order status',
    ],
];
