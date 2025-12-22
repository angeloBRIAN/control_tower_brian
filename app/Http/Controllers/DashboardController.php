<?php

namespace App\Http\Controllers;

use App\Models\DismissedDuplicateGroup;
use App\Models\DropdownOption;
use App\Models\Job;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with cached statistics.
     */
    public function index()
    {
        // Cache dashboard stats for 5 minutes
        $stats = Cache::remember('dashboard_stats', 300, function () {
            return [
                'uninvoiced' => Job::uninvoiced()->count(),
                'invoiced' => Job::invoiced()->count(),
                'needs_parts' => Job::uninvoiced()->needsParts()->count(),
                'vehicles_in_workshop' => Vehicle::where('is_in_workshop', true)->count(),
            ];
        });

        // Cache work status counts for 5 minutes
        $workStatusCounts = Cache::remember('dashboard_work_status', 300, function () {
            return Job::uninvoiced()
                ->selectRaw('COALESCE(work_status, "pending") as work_status, COUNT(*) as count')
                ->groupBy('work_status')
                ->get()
                ->keyBy('work_status');
        });

        // Cache dropdown options (rarely changes)
        $workStatusOptions = Cache::remember('dropdown_work_status', 3600, function () {
            return DropdownOption::getOptions('work_status');
        });

        // Cache duplicate count for 10 minutes (expensive operation)
        $duplicateCustomerCount = Cache::remember('dashboard_duplicates', 600, function () {
            return $this->countDuplicateCustomers();
        });

        // Cache chart data for 5 minutes
        $chartData = Cache::remember('dashboard_charts', 300, function () use ($workStatusOptions, $workStatusCounts) {
            return $this->getChartData($workStatusOptions, $workStatusCounts);
        });

        // Recent jobs with eager loading (not cached, always fresh)
        $recentJobs = Job::uninvoiced()
            ->with('vehicle')
            ->latest()
            ->take(5)
            ->get();

        $needsPartsJobs = Job::uninvoiced()
            ->needsParts()
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'stats' => $stats,
            'workStatusCounts' => $workStatusCounts,
            'workStatusOptions' => $workStatusOptions,
            'duplicateCustomerCount' => $duplicateCustomerCount,
            'chartData' => $chartData,
            'recentJobs' => $recentJobs,
            'needsPartsJobs' => $needsPartsJobs,
        ]);
    }

    /**
     * Count duplicate customer names (expensive operation).
     */
    protected function countDuplicateCustomers(): int
    {
        try {
            $allNames = DB::table(
                DB::raw("(
                    SELECT DISTINCT customer_name as name FROM vehicles WHERE customer_name IS NOT NULL AND customer_name != ''
                    UNION
                    SELECT DISTINCT customer_name as name FROM jobs WHERE customer_name IS NOT NULL AND customer_name != ''
                ) as customers")
            )->pluck('name')->toArray();

            $processed = [];
            $count = 0;

            foreach ($allNames as $name1) {
                if (in_array($name1, $processed)) continue;
                $similar = [$name1];
                $normalized1 = strtoupper(preg_replace('/\s+/', ' ', preg_replace('/[^A-Z0-9\s]/i', ' ', trim($name1))));

                foreach ($allNames as $name2) {
                    if ($name1 === $name2 || in_array($name2, $processed)) continue;
                    $normalized2 = strtoupper(preg_replace('/\s+/', ' ', preg_replace('/[^A-Z0-9\s]/i', ' ', trim($name2))));

                    $levenshtein = levenshtein($normalized1, $normalized2);
                    $maxLen = max(strlen($normalized1), strlen($normalized2));
                    $similarity = $maxLen > 0 ? (1 - $levenshtein / $maxLen) * 100 : 0;

                    similar_text($normalized1, $normalized2, $percentSimilar);

                    if (($similarity > 90 && $percentSimilar > 85) || ($similarity > 85 && $percentSimilar > 90)) {
                        $similar[] = $name2;
                        $processed[] = $name2;
                    }
                }
                $processed[] = $name1;

                if (count($similar) >= 2) {
                    if (!DismissedDuplicateGroup::isDismissed($similar)) {
                        $count++;
                    }
                }
            }

            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get chart data for dashboard.
     */
    protected function getChartData($workStatusOptions, $workStatusCounts): array
    {
        // Last 7 days job trend
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $last7Days->push([
                'date' => $date->format('d M'),
                'invoiced' => Job::whereDate('invoiced_at', $date)->count(),
                'new' => Job::whereDate('job_date', $date)->count(),
            ]);
        }

        // Status pie chart data
        $statusCounts = $workStatusOptions->map(fn($opt) => [
            'label' => $opt->label,
            'count' => $workStatusCounts->get($opt->value)?->count ?? 0,
            'color' => match ($opt->color) {
                'primary' => '#0d6efd',
                'success' => '#198754',
                'warning' => '#ffc107',
                'danger' => '#dc3545',
                'info' => '#0dcaf0',
                'secondary' => '#6c757d',
                default => '#6c757d'
            }
        ])->filter(fn($s) => $s['count'] > 0);

        // SA Revenue (Top 5)
        $saRevenue = Job::uninvoiced()
            ->selectRaw('service_advisor, SUM(COALESCE(total_sales, 0)) as revenue, COUNT(*) as job_count')
            ->whereNotNull('service_advisor')
            ->groupBy('service_advisor')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Job Aging breakdown
        $today = now()->startOfDay();
        $agingData = [
            ['label' => '< 3 days', 'count' => Job::uninvoiced()->where('job_date', '>', $today->copy()->subDays(3))->count(), 'color' => '#198754'],
            ['label' => '3-7 days', 'count' => Job::uninvoiced()->whereBetween('job_date', [$today->copy()->subDays(7), $today->copy()->subDays(3)])->count(), 'color' => '#0dcaf0'],
            ['label' => '7-14 days', 'count' => Job::uninvoiced()->whereBetween('job_date', [$today->copy()->subDays(14), $today->copy()->subDays(7)])->count(), 'color' => '#ffc107'],
            ['label' => '14-30 days', 'count' => Job::uninvoiced()->whereBetween('job_date', [$today->copy()->subDays(30), $today->copy()->subDays(14)])->count(), 'color' => '#fd7e14'],
            ['label' => '> 30 days', 'count' => Job::uninvoiced()->where('job_date', '<', $today->copy()->subDays(30))->count(), 'color' => '#dc3545'],
        ];

        return [
            'last7Days' => $last7Days,
            'statusCounts' => $statusCounts,
            'saRevenue' => $saRevenue,
            'agingData' => $agingData,
        ];
    }

    /**
     * Clear dashboard cache (called after data changes).
     */
    public static function clearCache(): void
    {
        Cache::forget('dashboard_stats');
        Cache::forget('dashboard_work_status');
        Cache::forget('dashboard_duplicates');
        Cache::forget('dashboard_charts');
    }
}
