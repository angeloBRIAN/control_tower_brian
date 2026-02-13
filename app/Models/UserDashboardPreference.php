<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User Dashboard Preference Model.
 * 
 * Stores each user's personalized dashboard widget configuration.
 * 
 * @property int $id
 * @property int $user_id
 * @property array|null $widget_config
 * @property array|null $theme_settings
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class UserDashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_config',
        'theme_settings',
    ];

    protected $casts = [
        'widget_config' => 'array',
        'theme_settings' => 'array',
    ];

    /**
     * Available widgets with their metadata.
     */
    const AVAILABLE_WIDGETS = [
        'stat_cards' => [
            'name' => 'Overview Stats',
            'description' => 'Uninvoiced, Needs Parts, Invoiced, In Workshop counts',
            'icon' => 'bar-chart-fill',
            'roles' => ['*'], // Available to all roles
            'default_size' => 'full',
        ],
        'my_jobs' => [
            'name' => 'My Jobs',
            'description' => 'Jobs assigned to you',
            'icon' => 'briefcase-fill',
            'roles' => ['service_advisor', 'foreman', 'admin', 'manager'],
            'default_size' => 'half',
        ],
        'work_status' => [
            'name' => 'Work Status Breakdown',
            'description' => 'Status distribution with counts',
            'icon' => 'pie-chart-fill',
            'roles' => ['*'],
            'default_size' => 'full',
        ],
        'recent_jobs' => [
            'name' => 'Recent Jobs',
            'description' => 'Last 5 jobs in system',
            'icon' => 'clock-history',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        'needs_parts' => [
            'name' => 'Needs Parts',
            'description' => 'Jobs waiting for parts',
            'icon' => 'gear-fill',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        'parts_tracking' => [
            'name' => 'Parts Tracking',
            'description' => 'Pending, Due Soon, Overdue orders',
            'icon' => 'box-seam-fill',
            'roles' => ['sparepart', 'admin', 'manager'],
            'default_size' => 'full',
        ],
        'job_trend_chart' => [
            'name' => 'Job Trend (7 Days)',
            'description' => 'New vs Invoiced jobs chart',
            'icon' => 'graph-up-arrow',
            'roles' => ['manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'aging_breakdown' => [
            'name' => 'Job Aging',
            'description' => 'Age distribution of uninvoiced jobs',
            'icon' => 'hourglass-split',
            'roles' => ['manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'job_type_distribution' => [
            'name' => 'Job Type Distribution',
            'description' => 'Distribution of active jobs by type',
            'icon' => 'pie-chart-fill',
            'roles' => ['manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'sa_revenue' => [
            'name' => 'SA Revenue Ranking',
            'description' => 'Top 5 Service Advisors by revenue',
            'icon' => 'trophy-fill',
            'roles' => ['manager', 'admin', 'finance'],
            'default_size' => 'half',
        ],
        'monthly_completion' => [
            'name' => 'Monthly Completion Rate',
            'description' => 'Completed vs Open jobs for specific month',
            'icon' => 'pie-chart',
            'roles' => ['manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'quick_actions' => [
            'name' => 'Quick Actions',
            'description' => 'Add Job, View Kanban, Reports shortcuts',
            'icon' => 'lightning-fill',
            'roles' => ['*'],
            'default_size' => 'full',
        ],
        'bookings_today' => [
            'name' => "Today's Bookings",
            'description' => 'Scheduled bookings for today',
            'icon' => 'calendar-check-fill',
            'roles' => ['service_advisor', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'pending_invoices' => [
            'name' => 'Pending Invoices',
            'description' => 'Invoices awaiting payment',
            'icon' => 'receipt',
            'roles' => ['finance', 'admin', 'manager'],
            'default_size' => 'half',
        ],
        'saved_filters' => [
            'name' => 'My Saved Filters',
            'description' => 'Quick access to your saved report filters',
            'icon' => 'bookmark-fill',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        // Productivity & Analytics Widgets
        'notifications' => [
            'name' => 'Notifications',
            'description' => 'Recent unread notifications with quick actions',
            'icon' => 'bell-fill',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        'my_performance' => [
            'name' => 'My Performance',
            'description' => 'Your personal KPIs (jobs closed, revenue, avg time)',
            'icon' => 'graph-up-arrow',
            'roles' => ['service_advisor', 'foreman', 'admin', 'manager'],
            'default_size' => 'half',
        ],
        'team_workload' => [
            'name' => 'Team Workload',
            'description' => 'Visual chart of technician assignments',
            'icon' => 'people-fill',
            'roles' => ['foreman', 'manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'sla_alerts' => [
            'name' => 'SLA Alerts',
            'description' => 'Jobs approaching or exceeding SLA thresholds',
            'icon' => 'exclamation-triangle-fill',
            'roles' => ['manager', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'customer_followups' => [
            'name' => 'Customer Follow-ups',
            'description' => 'Customers due for follow-up calls/reminders',
            'icon' => 'telephone-outbound-fill',
            'roles' => ['service_advisor', 'admin', 'manager'],
            'default_size' => 'half',
        ],
        'overdue_jobs' => [
            'name' => 'Overdue Jobs',
            'description' => 'Jobs exceeding threshold days uninvoiced',
            'icon' => 'clock-history',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        // Calendar & Scheduling Widgets
        'week_calendar' => [
            'name' => 'Week Calendar',
            'description' => 'Mini calendar showing bookings/jobs this week',
            'icon' => 'calendar-week',
            'roles' => ['service_advisor', 'foreman', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'upcoming_pdi' => [
            'name' => 'Upcoming PDI',
            'description' => 'Scheduled PDI inspections',
            'icon' => 'clipboard-check-fill',
            'roles' => ['service_advisor', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        'towing_schedule' => [
            'name' => 'Towing Schedule',
            'description' => 'Upcoming towing pickups',
            'icon' => 'truck',
            'roles' => ['service_advisor', 'admin', 'control_tower'],
            'default_size' => 'half',
        ],
        // Finance Widgets
        'daily_revenue' => [
            'name' => 'Daily Revenue',
            'description' => "Today's invoiced amount vs target",
            'icon' => 'cash-stack',
            'roles' => ['finance', 'manager', 'admin'],
            'default_size' => 'half',
        ],
        'receivables_aging' => [
            'name' => 'Receivables Aging',
            'description' => 'Overdue payment breakdown',
            'icon' => 'receipt-cutoff',
            'roles' => ['finance', 'admin', 'manager'],
            'default_size' => 'half',
        ],
        'top_customers' => [
            'name' => 'Top Customers',
            'description' => 'Highest value customers this month',
            'icon' => 'star-fill',
            'roles' => ['manager', 'admin', 'finance'],
            'default_size' => 'half',
        ],
        // Alerts & System Widgets
        'system_alerts' => [
            'name' => 'System Status',
            'description' => 'Backup status, import errors, disk usage',
            'icon' => 'gear-wide-connected',
            'roles' => ['admin'],
            'default_size' => 'half',
        ],
        'announcements' => [
            'name' => 'Announcements',
            'description' => 'Company-wide announcements/notices',
            'icon' => 'megaphone-fill',
            'roles' => ['*'],
            'default_size' => 'half',
        ],
        'activity_feed' => [
            'name' => 'Activity Feed',
            'description' => 'Recent activity on jobs you are involved in',
            'icon' => 'activity',
            'roles' => ['service_advisor', 'foreman', 'admin', 'manager'],
            'default_size' => 'half',
        ],
    ];

    /**
     * Default widget configurations per role.
     */
    const ROLE_DEFAULTS = [
        'service_advisor' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'my_jobs', 'enabled' => true, 'position' => 2],
            ['id' => 'my_performance', 'enabled' => true, 'position' => 3],
            ['id' => 'bookings_today', 'enabled' => true, 'position' => 4],
            ['id' => 'customer_followups', 'enabled' => true, 'position' => 5],
            ['id' => 'work_status', 'enabled' => true, 'position' => 6],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 7],
        ],
        'foreman' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'my_jobs', 'enabled' => true, 'position' => 2],
            ['id' => 'team_workload', 'enabled' => true, 'position' => 3],
            ['id' => 'my_performance', 'enabled' => true, 'position' => 4],
            ['id' => 'needs_parts', 'enabled' => true, 'position' => 5],
            ['id' => 'work_status', 'enabled' => true, 'position' => 6],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 7],
        ],
        'finance' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'daily_revenue', 'enabled' => true, 'position' => 2],
            ['id' => 'pending_invoices', 'enabled' => true, 'position' => 3],
            ['id' => 'receivables_aging', 'enabled' => true, 'position' => 4],
            ['id' => 'sa_revenue', 'enabled' => true, 'position' => 5],
            ['id' => 'top_customers', 'enabled' => true, 'position' => 6],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 7],
        ],
        'sparepart' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'parts_tracking', 'enabled' => true, 'position' => 2],
            ['id' => 'needs_parts', 'enabled' => true, 'position' => 3],
            ['id' => 'overdue_jobs', 'enabled' => true, 'position' => 4],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 5],
        ],
        'manager' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'sla_alerts', 'enabled' => true, 'position' => 2],
            ['id' => 'job_trend_chart', 'enabled' => true, 'position' => 3],
            ['id' => 'daily_revenue', 'enabled' => true, 'position' => 4],
            ['id' => 'team_workload', 'enabled' => true, 'position' => 5],
            ['id' => 'aging_breakdown', 'enabled' => true, 'position' => 6],
            ['id' => 'sa_revenue', 'enabled' => true, 'position' => 7],
            ['id' => 'top_customers', 'enabled' => true, 'position' => 8],
            ['id' => 'work_status', 'enabled' => true, 'position' => 9],
            ['id' => 'job_type_distribution', 'enabled' => true, 'position' => 10],
            ['id' => 'monthly_completion', 'enabled' => true, 'position' => 11],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 12],
        ],
        'admin' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'system_alerts', 'enabled' => true, 'position' => 2],
            ['id' => 'sla_alerts', 'enabled' => true, 'position' => 3],
            ['id' => 'work_status', 'enabled' => true, 'position' => 4],
            ['id' => 'job_trend_chart', 'enabled' => true, 'position' => 5],
            ['id' => 'daily_revenue', 'enabled' => true, 'position' => 6],
            ['id' => 'aging_breakdown', 'enabled' => true, 'position' => 7],
            ['id' => 'team_workload', 'enabled' => true, 'position' => 8],
            ['id' => 'job_type_distribution', 'enabled' => true, 'position' => 9],
            ['id' => 'activity_feed', 'enabled' => true, 'position' => 10],
            ['id' => 'monthly_completion', 'enabled' => true, 'position' => 11],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 12],
        ],
        'control_tower' => [
            ['id' => 'stat_cards', 'enabled' => true, 'position' => 0],
            ['id' => 'notifications', 'enabled' => true, 'position' => 1],
            ['id' => 'sla_alerts', 'enabled' => true, 'position' => 2],
            ['id' => 'work_status', 'enabled' => true, 'position' => 3],
            ['id' => 'team_workload', 'enabled' => true, 'position' => 4],
            ['id' => 'job_trend_chart', 'enabled' => true, 'position' => 5],
            ['id' => 'job_type_distribution', 'enabled' => true, 'position' => 6],
            ['id' => 'overdue_jobs', 'enabled' => true, 'position' => 7],
            ['id' => 'upcoming_pdi', 'enabled' => true, 'position' => 8],
            ['id' => 'towing_schedule', 'enabled' => true, 'position' => 9],
            ['id' => 'monthly_completion', 'enabled' => true, 'position' => 10],
            ['id' => 'quick_actions', 'enabled' => true, 'position' => 11],
        ],
    ];

    /**
     * Relationship to User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the effective widget configuration.
     * Returns user's config or role defaults if not set.
     */
    public function getEffectiveWidgets(): array
    {
        if (!empty($this->widget_config['widgets'])) {
            return $this->widget_config['widgets'];
        }

        return self::getDefaultForRole($this->user->role);
    }

    /**
     * Get enabled widgets sorted by position.
     */
    public function getEnabledWidgets(): array
    {
        $widgets = $this->getEffectiveWidgets();
        
        // Filter enabled and sort by position
        $enabled = array_filter($widgets, fn($w) => $w['enabled'] ?? true);
        usort($enabled, fn($a, $b) => ($a['position'] ?? 0) <=> ($b['position'] ?? 0));
        
        return $enabled;
    }

    /**
     * Update widget configuration.
     */
    public function setWidgetConfig(array $widgets): self
    {
        $this->widget_config = ['widgets' => $widgets];
        $this->save();
        
        return $this;
    }

    /**
     * Reset to role default configuration.
     */
    public function resetToDefault(): self
    {
        $this->widget_config = ['widgets' => self::getDefaultForRole($this->user->role)];
        $this->save();
        
        return $this;
    }

    /**
     * Get default widget configuration for a role.
     */
    public static function getDefaultForRole(string $role): array
    {
        return self::ROLE_DEFAULTS[$role] ?? self::ROLE_DEFAULTS['control_tower'];
    }

    /**
     * Get widgets available for a specific role.
     */
    public static function getAvailableWidgetsForRole(string $role): array
    {
        return array_filter(self::AVAILABLE_WIDGETS, function ($widget) use ($role) {
            return in_array('*', $widget['roles']) || in_array($role, $widget['roles']);
        });
    }

    /**
     * Get or create preference for a user.
     */
    public static function getOrCreateForUser(User $user): self
    {
        return self::firstOrCreate(
            ['user_id' => $user->id],
            ['widget_config' => ['widgets' => self::getDefaultForRole($user->role)]]
        );
    }
}
