<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SchedulerController extends Controller
{
    /**
     * Get list of scheduled tasks
     */
    public function index()
    {
        $schedules = $this->getScheduledTasks();
        
        return view('admin.scheduler.index', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Run a scheduled task manually
     */
    public function runNow(Request $request)
    {
        $command = $request->input('command');
        
        $allowedCommands = [
            'customers:find-duplicates',
            'reports:send',
            'report:weekly',
        ];

        if (!in_array($command, $allowedCommands)) {
            return redirect()->back()->with('error', 'Command not allowed.');
        }

        try {
            Artisan::call($command);
            $output = Artisan::output();
            
            return redirect()->back()->with('success', "Command '{$command}' executed successfully. Output: " . substr($output, 0, 200));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Command failed: " . $e->getMessage());
        }
    }

    /**
     * Get scheduled tasks from console.php
     */
    private function getScheduledTasks(): array
    {
        return [
            [
                'name' => 'Weekly Report',
                'command' => 'report:weekly',
                'schedule' => 'Weekly (Monday at 08:00)',
                'description' => 'Send weekly workshop report to admins and managers',
                'next_run' => $this->getNextRun('weekly', 1, '08:00'),
            ],
            [
                'name' => 'Customer Duplicate Scan',
                'command' => 'customers:find-duplicates',
                'schedule' => 'Daily at 07:00',
                'description' => 'Scan for duplicate customer names and update cache table',
                'next_run' => $this->getNextRun('daily', null, '07:00'),
            ],
            [
                'name' => 'Scheduled Email Reports',
                'command' => 'reports:send',
                'schedule' => 'Every minute',
                'description' => 'Check and send scheduled email reports based on their individual schedules',
                'next_run' => now()->addMinute()->format('d M Y H:i'),
            ],
        ];
    }

    /**
     * Calculate next run time
     */
    private function getNextRun(string $frequency, ?int $dayOfWeek, string $time): string
    {
        $now = now()->timezone('Asia/Jakarta');
        [$hour, $minute] = explode(':', $time);

        if ($frequency === 'daily') {
            $next = $now->copy()->setTime($hour, $minute);
            if ($next->isPast()) {
                $next->addDay();
            }
            return $next->format('d M Y H:i');
        }

        if ($frequency === 'weekly') {
            $next = $now->copy()->startOfWeek()->addDays($dayOfWeek)->setTime($hour, $minute);
            if ($next->isPast()) {
                $next->addWeek();
            }
            return $next->format('d M Y H:i');
        }

        return 'Unknown';
    }
}
