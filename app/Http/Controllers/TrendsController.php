<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobInvoice;
use App\Models\ServiceAdvisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrendsController extends Controller
{
    /**
     * Main trends dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'week'); // week, month, quarter
        
        // Get period comparison data
        $periodData = $this->getPeriodComparison($period);
        
        // Get SA performance trends (last 6 months)
        $saPerformance = $this->getSaPerformanceTrends();
        
        // Get franchise comparison
        $franchiseData = $this->getFranchiseComparison();
        
        // Get aging trends (last 4 weeks)
        $agingData = $this->getAgingTrends();
        
        return view('reports.trends', compact(
            'period',
            'periodData',
            'saPerformance',
            'franchiseData',
            'agingData'
        ));
    }

    /**
     * Calculate period-over-period comparison
     */
    protected function getPeriodComparison(string $period): array
    {
        switch ($period) {
            case 'month':
                $currentStart = Carbon::now()->startOfMonth();
                $currentEnd = Carbon::now();
                $previousStart = Carbon::now()->subMonth()->startOfMonth();
                $previousEnd = Carbon::now()->subMonth()->endOfMonth();
                $periodLabel = 'This Month vs Last Month';
                break;
            case 'quarter':
                $currentStart = Carbon::now()->startOfQuarter();
                $currentEnd = Carbon::now();
                $previousStart = Carbon::now()->subQuarter()->startOfQuarter();
                $previousEnd = Carbon::now()->subQuarter()->endOfQuarter();
                $periodLabel = 'This Quarter vs Last Quarter';
                break;
            default: // week
                $currentStart = Carbon::now()->startOfWeek();
                $currentEnd = Carbon::now();
                $previousStart = Carbon::now()->subWeek()->startOfWeek();
                $previousEnd = Carbon::now()->subWeek()->endOfWeek();
                $periodLabel = 'This Week vs Last Week';
        }

        // Current period metrics
        $currentNew = Job::whereBetween('created_at', [$currentStart, $currentEnd])->count();
        $currentInvoiced = Job::where('status', 'invoiced')
            ->whereBetween('invoiced_at', [$currentStart, $currentEnd])->count();
        $currentRevenue = JobInvoice::whereBetween('invoice_date', [$currentStart, $currentEnd])
            ->sum('inv_ppn_meterai');
        $currentAvgDays = Job::where('status', 'invoiced')
            ->whereBetween('invoiced_at', [$currentStart, $currentEnd])
            ->whereNotNull('created_at')
            ->selectRaw('AVG(DATEDIFF(invoiced_at, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        // Previous period metrics
        $previousNew = Job::whereBetween('created_at', [$previousStart, $previousEnd])->count();
        $previousInvoiced = Job::where('status', 'invoiced')
            ->whereBetween('invoiced_at', [$previousStart, $previousEnd])->count();
        $previousRevenue = JobInvoice::whereBetween('invoice_date', [$previousStart, $previousEnd])
            ->sum('inv_ppn_meterai');
        $previousAvgDays = Job::where('status', 'invoiced')
            ->whereBetween('invoiced_at', [$previousStart, $previousEnd])
            ->whereNotNull('created_at')
            ->selectRaw('AVG(DATEDIFF(invoiced_at, created_at)) as avg_days')
            ->value('avg_days') ?? 0;

        return [
            'label' => $periodLabel,
            'currentPeriod' => $currentStart->format('M d') . ' - ' . $currentEnd->format('M d, Y'),
            'previousPeriod' => $previousStart->format('M d') . ' - ' . $previousEnd->format('M d, Y'),
            'metrics' => [
                'new_jobs' => [
                    'current' => $currentNew,
                    'previous' => $previousNew,
                    'change' => $this->calculateChange($currentNew, $previousNew),
                ],
                'invoiced' => [
                    'current' => $currentInvoiced,
                    'previous' => $previousInvoiced,
                    'change' => $this->calculateChange($currentInvoiced, $previousInvoiced),
                ],
                'revenue' => [
                    'current' => $currentRevenue,
                    'previous' => $previousRevenue,
                    'change' => $this->calculateChange($currentRevenue, $previousRevenue),
                ],
                'avg_days' => [
                    'current' => round($currentAvgDays, 1),
                    'previous' => round($previousAvgDays, 1),
                    'change' => $this->calculateChange($previousAvgDays, $currentAvgDays), // Reversed - lower is better
                ],
            ],
        ];
    }

    /**
     * Get SA performance trends over 6 months
     */
    protected function getSaPerformanceTrends(): array
    {
        $months = [];
        $saData = [];
        
        // Get last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            // Get top 5 SAs with their close rates
            $saStats = Job::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->whereNotNull('service_advisor')
                ->select('service_advisor')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN status = "invoiced" THEN 1 ELSE 0 END) as invoiced')
                ->groupBy('service_advisor')
                ->having('total', '>=', 5) // Only SAs with 5+ jobs
                ->get();
            
            foreach ($saStats as $stat) {
                $sa = $stat->service_advisor;
                if (!isset($saData[$sa])) {
                    $saData[$sa] = array_fill(0, 6, null);
                }
                $closeRate = $stat->total > 0 ? round(($stat->invoiced / $stat->total) * 100, 1) : 0;
                $saData[$sa][5 - $i] = $closeRate;
            }
        }

        // Sort by latest month close rate and take top 5
        uasort($saData, function($a, $b) {
            return ($b[5] ?? 0) <=> ($a[5] ?? 0);
        });
        $saData = array_slice($saData, 0, 5, true);

        return [
            'labels' => $months,
            'datasets' => $saData,
        ];
    }

    /**
     * Get franchise comparison (PC vs CV)
     */
    protected function getFranchiseComparison(): array
    {
        $thisMonth = Carbon::now()->startOfMonth();
        
        $pcStats = Job::where('franchise', 'PC')
            ->where('created_at', '>=', $thisMonth)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "invoiced" THEN 1 ELSE 0 END) as invoiced')
            ->selectRaw('AVG(CASE WHEN status = "invoiced" THEN DATEDIFF(invoiced_at, created_at) END) as avg_days')
            ->selectRaw('SUM(CASE WHEN need_part = 1 THEN 1 ELSE 0 END) as parts_pending')
            ->first();
            
        $cvStats = Job::where('franchise', 'CV')
            ->where('created_at', '>=', $thisMonth)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = "invoiced" THEN 1 ELSE 0 END) as invoiced')
            ->selectRaw('AVG(CASE WHEN status = "invoiced" THEN DATEDIFF(invoiced_at, created_at) END) as avg_days')
            ->selectRaw('SUM(CASE WHEN need_part = 1 THEN 1 ELSE 0 END) as parts_pending')
            ->first();

        $pcRevenue = JobInvoice::whereHas('job', fn($q) => $q->where('franchise', 'PC'))
            ->where('invoice_date', '>=', $thisMonth)
            ->sum('inv_ppn_meterai');
            
        $cvRevenue = JobInvoice::whereHas('job', fn($q) => $q->where('franchise', 'CV'))
            ->where('invoice_date', '>=', $thisMonth)
            ->sum('inv_ppn_meterai');

        return [
            'pc' => [
                'total' => $pcStats->total ?? 0,
                'invoiced' => $pcStats->invoiced ?? 0,
                'avg_days' => round($pcStats->avg_days ?? 0, 1),
                'parts_pending' => $pcStats->parts_pending ?? 0,
                'parts_pct' => $pcStats->total > 0 ? round(($pcStats->parts_pending / $pcStats->total) * 100, 1) : 0,
                'revenue' => $pcRevenue,
                'avg_value' => $pcStats->invoiced > 0 ? $pcRevenue / $pcStats->invoiced : 0,
            ],
            'cv' => [
                'total' => $cvStats->total ?? 0,
                'invoiced' => $cvStats->invoiced ?? 0,
                'avg_days' => round($cvStats->avg_days ?? 0, 1),
                'parts_pending' => $cvStats->parts_pending ?? 0,
                'parts_pct' => $cvStats->total > 0 ? round(($cvStats->parts_pending / $cvStats->total) * 100, 1) : 0,
                'revenue' => $cvRevenue,
                'avg_value' => $cvStats->invoiced > 0 ? $cvRevenue / $cvStats->invoiced : 0,
            ],
        ];
    }

    /**
     * Get aging trends (last 4 weeks)
     */
    protected function getAgingTrends(): array
    {
        $weeks = [];
        $data = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            // For current week, use now as end date
            if ($i === 0) {
                $weekEnd = Carbon::now();
            }
            
            $weeks[] = 'Week ' . (4 - $i);
            
            // Count jobs aged > 7 days at that point
            $agedOver7 = Job::where('status', 'uninvoiced')
                ->where('created_at', '<', $weekEnd->copy()->subDays(7))
                ->where('created_at', '<=', $weekEnd)
                ->count();
                
            $agedOver14 = Job::where('status', 'uninvoiced')
                ->where('created_at', '<', $weekEnd->copy()->subDays(14))
                ->where('created_at', '<=', $weekEnd)
                ->count();
                
            $agedOver30 = Job::where('status', 'uninvoiced')
                ->where('created_at', '<', $weekEnd->copy()->subDays(30))
                ->where('created_at', '<=', $weekEnd)
                ->count();
            
            $data[] = [
                'over7' => $agedOver7,
                'over14' => $agedOver14,
                'over30' => $agedOver30,
            ];
        }

        return [
            'labels' => $weeks,
            'data' => $data,
        ];
    }

    /**
     * Calculate percentage change
     */
    protected function calculateChange($current, $previous): array
    {
        if ($previous == 0) {
            return ['value' => $current > 0 ? 100 : 0, 'direction' => $current > 0 ? 'up' : 'neutral'];
        }
        
        $change = (($current - $previous) / $previous) * 100;
        
        return [
            'value' => abs(round($change, 1)),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }
}
