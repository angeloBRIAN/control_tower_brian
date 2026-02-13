<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Job;
use App\Models\JobInvoice;
use App\Models\SavedReport;
use App\Models\UserDashboardPreference;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Dashboard Settings Controller.
 * 
 * Handles user dashboard customization, widget configuration,
 * and preference management.
 */
class DashboardSettingsController extends Controller
{
    /**
     * Show dashboard customization page.
     */
    public function index(): View
    {
        $user = auth()->user();
        $preference = $user->getDashboardPreference();
        
        // Get available widgets for this user's role
        $availableWidgets = UserDashboardPreference::getAvailableWidgetsForRole($user->role);
        
        // Get current widget configuration
        $currentWidgets = $preference->getEffectiveWidgets();
        
        // Map current config to widget metadata
        $widgetsWithMeta = [];
        foreach ($currentWidgets as $widget) {
            $widgetId = $widget['id'];
            if (isset($availableWidgets[$widgetId])) {
                $widgetsWithMeta[] = array_merge($availableWidgets[$widgetId], [
                    'id' => $widgetId,
                    'enabled' => $widget['enabled'] ?? true,
                    'position' => $widget['position'] ?? 0,
                ]);
            }
        }
        
        // Get role defaults to check if a new widget should be enabled by default
        $roleDefaults = collect(UserDashboardPreference::getDefaultForRole($user->role))->keyBy('id');

        // Add any available widgets not in current config
        foreach ($availableWidgets as $id => $meta) {
            $exists = collect($widgetsWithMeta)->contains('id', $id);
            if (!$exists) {
                // Check if this widget is enabled in role defaults
                $defaultConfig = $roleDefaults->get($id);
                $isDefaultEnabled = $defaultConfig['enabled'] ?? false;
                
                $widgetsWithMeta[] = array_merge($meta, [
                    'id' => $id,
                    'enabled' => $isDefaultEnabled, // Use role default instead of hardcoded false
                    'position' => count($widgetsWithMeta),
                ]);
            }
        }
        
        // Sort by position
        usort($widgetsWithMeta, fn($a, $b) => $a['position'] <=> $b['position']);
        
        return view('dashboard.customize', [
            'widgets' => $widgetsWithMeta,
            'preference' => $preference,
        ]);
    }

    /**
     * Save widget configuration.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $preference = $user->getDashboardPreference();
        
        $enabledWidgets = $request->input('widgets', []);
        $positions = $request->input('positions', []);
        
        // Build new config
        $widgets = [];
        foreach ($positions as $position => $widgetId) {
            $widgets[] = [
                'id' => $widgetId,
                'enabled' => in_array($widgetId, $enabledWidgets),
                'position' => (int) $position,
            ];
        }
        
        $preference->setWidgetConfig($widgets);
        
        return redirect()->route('dashboard')
            ->with('success', 'Dashboard customization saved successfully!');
    }

    /**
     * Reset to role defaults.
     */
    public function reset(): RedirectResponse
    {
        $user = auth()->user();
        $preference = $user->getDashboardPreference();
        $preference->resetToDefault();
        
        return redirect()->route('dashboard')
            ->with('success', 'Dashboard reset to default configuration.');
    }

    /**
     * Reorder widgets via AJAX.
     */
    public function reorder(Request $request)
    {
        $user = auth()->user();
        $preference = $user->getDashboardPreference();
        
        $order = $request->input('order', []);
        
        $currentWidgets = $preference->getEffectiveWidgets();
        $widgetMap = collect($currentWidgets)->keyBy('id');
        
        $newConfig = [];
        foreach ($order as $position => $widgetId) {
            $existing = $widgetMap->get($widgetId);
            $newConfig[] = [
                'id' => $widgetId,
                'enabled' => $existing['enabled'] ?? true,
                'position' => (int) $position,
            ];
        }
        
        $preference->setWidgetConfig($newConfig);
        
        return response()->json(['success' => true]);
    }

    /**
     * Get widget-specific data (for AJAX loading).
     */
    public function getWidgetData(Request $request, string $widgetId)
    {
        $user = auth()->user();
        
        \Illuminate\Support\Facades\Log::info("Dashboard Widget Fetch: User {$user->id} ({$user->role}) requested {$widgetId}");
        
        return match ($widgetId) {
            'my_jobs' => $this->getMyJobsData($user),
            'bookings_today' => $this->getBookingsTodayData(),
            'pending_invoices' => $this->getPendingInvoicesData(),
            'saved_filters' => $this->getSavedFiltersData($user),
            'monthly_completion' => $this->getMonthlyCompletionData($request),
            'job_type_distribution' => $this->getJobTypeDistributionData($request),
            default => response()->json(['error' => 'Unknown widget'], 404),
        };
    }

    /**
     * Get "My Jobs" data for current user.
     */
    protected function getMyJobsData($user): array
    {
        $query = Job::uninvoiced()->latest();
        
        // Filter by SA or Foreman if user is linked
        if ($user->serviceAdvisor) {
            $query->where('service_advisor', $user->serviceAdvisor->name);
        } elseif ($user->foreman) {
            $query->where('foreman', $user->foreman->name);
        }
        
        return ['myJobs' => $query->take(10)->get()];
    }

    /**
     * Get today's bookings.
     */
    protected function getBookingsTodayData(): array
    {
        $bookings = Booking::whereDate('booking_date', today())
            ->orderBy('booking_time')
            ->take(5)
            ->get();
            
        return ['bookingsToday' => $bookings];
    }

    /**
     * Get pending invoices.
     */
    protected function getPendingInvoicesData(): array
    {
        $invoices = JobInvoice::whereIn('status', ['pending', 'partially_paid'])
            ->with('job')
            ->orderByDesc('invoice_date')
            ->take(5)
            ->get();
            
        return ['pendingInvoices' => $invoices];
    }

    /**
     * Get saved filters for user.
     */
    protected function getSavedFiltersData($user): array
    {
        $filters = SavedReport::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();
            
        return ['savedFilters' => $filters];
    }

    /**
     * Get monthly completion data (Completed vs Open).
     */
    protected function getMonthlyCompletionData(Request $request): array
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Define completed statuses (11. Invoice, 12. Tunggu Bayar, 13. Lunas)
        // Using strict string matching based on Job::WORK_STATUSES
        $completedStatuses = [
            '11. Proses Invoice',
            '12. Menunggu Pembayaran',
            '13. Sudah Dibayar'
        ];
        
        // Query jobs for the selected month/year
        $query = Job::withoutGlobalScopes()
            ->whereYear('job_date', $year)
            ->whereMonth('job_date', $month);
            
        // Clone query for counts
        $total = (clone $query)->count();
        $completed = (clone $query)->whereIn('work_status', $completedStatuses)->count();
        $open = $total - $completed;
        
        return [
            'monthlyCompletion' => [
                'total' => $total,
                'completed' => $completed,
                'open' => $open,
                'month_name' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'),
                'chart_data' => [
                    'labels' => ['Completed (Invoiced)', 'Open (In Progress)'],
                    'data' => [$completed, $open],
                ]
            ]
        ];
    }

    /**
     * Get Job Type Distribution data (Stacked Bar Chart by Date).
     */
    protected function getJobTypeDistributionData(Request $request): array
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        // Groups definition
        $groups = [
            'Asuransi' => ['insurance', 'asuransi'],
            'Cash' => ['cash'],
            'Internal' => ['internal'], 
            'ISP & Warranty' => ['isp', 'warranty', 'campaign'],
            'PDI' => ['pdi'],
            // 'Other' will capture anything else
        ];

        // Fetch jobs for the month
        $jobs = Job::withoutGlobalScopes()
            ->whereYear('job_date', $year)
            ->whereMonth('job_date', $month)
            ->get(['job_date', 'job_type']);

        // Initialize daily data structure
        $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $dailyData = [];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $dailyData[$date] = [
                'Asuransi' => 0,
                'Cash' => 0,
                'Internal' => 0,
                'ISP & Warranty' => 0,
                'PDI' => 0,
                'Other' => 0,
            ];
        }

        // Populate counts
        foreach ($jobs as $job) {
            $date = $job->job_date ? $job->job_date->format('Y-m-d') : null;
            if (!$date || !isset($dailyData[$date])) continue;

            $type = strtolower($job->job_type ?? '');
            $foundGroup = false;

            foreach ($groups as $groupName => $types) {
                if (in_array($type, $types)) {
                    $dailyData[$date][$groupName]++;
                    $foundGroup = true;
                    break;
                }
            }

            if (!$foundGroup) {
                $dailyData[$date]['Other']++;
            }
        }

        // Format for Chart.js
        $labels = array_keys($dailyData);
        // Format labels to be dd/mm/yyyy
        $formattedLabels = array_map(function($date) {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        }, $labels);

        $datasets = [];
        $colors = [
            'Asuransi' => '#0d6efd', // Blue
            'Cash' => '#fd7e14', // Orange
            'Internal' => '#6c757d', // Gray
            'ISP & Warranty' => '#ffc107', // Yellow
            'PDI' => '#198754', // Green
            'Other' => '#6610f2' // Purple
        ];

        foreach (array_keys($groups) as $groupName) {
            $data = [];
            foreach ($dailyData as $dayStats) {
                $data[] = $dayStats[$groupName];
            }
            $datasets[] = [
                'label' => $groupName,
                'data' => $data,
                'backgroundColor' => $colors[$groupName],
            ];
        }
        
        // Add 'Other' dataset
        $otherData = [];
        foreach ($dailyData as $dayStats) {
            $otherData[] = $dayStats['Other'];
        }
        $datasets[] = [
            'label' => 'Other',
            'data' => $otherData,
            'backgroundColor' => $colors['Other'],
        ];

        return [
            'jobTypeDistribution' => [
                'labels' => $formattedLabels,
                'datasets' => $datasets,
                'month_name' => \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y'),
            ]
        ];
    }
}
