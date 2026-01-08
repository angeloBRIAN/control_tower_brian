<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\PartOrder;
use App\Models\User;
use Illuminate\Console\Command;

class NotifyPartsFulfillment extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'parts:notify {--days=7 : Days before expected date to start notifying}';

    /**
     * The console command description.
     */
    protected $description = 'Send notifications for upcoming part order fulfillment deadlines';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $reminderDays = (int) $this->option('days');
        
        // Get all sparepart users
        $sparepartUsers = User::where('role', 'sparepart')->get();
        
        if ($sparepartUsers->isEmpty()) {
            $this->info('No sparepart users found to notify.');
            return Command::SUCCESS;
        }

        // Get orders due within reminder days
        $dueSoonOrders = PartOrder::dueSoon($reminderDays)->with('job')->get();
        
        // Get overdue orders
        $overdueOrders = PartOrder::overdue()->with('job')->get();
        
        $notificationsSent = 0;

        // Notify about due soon orders
        if ($dueSoonOrders->isNotEmpty()) {
            foreach ($sparepartUsers as $user) {
                $message = $dueSoonOrders->count() . ' part order(s) due within ' . $reminderDays . ' days';
                
                $details = $dueSoonOrders->take(3)->map(function ($order) {
                    $days = $order->days_until_expected;
                    return "{$order->part_name} ({$order->job->job_number}) - {$days} days left";
                })->implode("\n");
                
                if ($dueSoonOrders->count() > 3) {
                    $details .= "\n+" . ($dueSoonOrders->count() - 3) . " more...";
                }
                
                Notification::notify(
                    $user->id,
                    Notification::TYPE_REMINDER,
                    $message,
                    $details,
                    route('parts.kanban'),
                    'clock-history',
                    'warning'
                );
                
                $notificationsSent++;
            }
            
            $this->info("Notified {$sparepartUsers->count()} user(s) about {$dueSoonOrders->count()} due soon order(s)");
        }

        // Notify about overdue orders
        if ($overdueOrders->isNotEmpty()) {
            foreach ($sparepartUsers as $user) {
                $message = '⚠️ ' . $overdueOrders->count() . ' OVERDUE part order(s) require attention!';
                
                $details = $overdueOrders->take(3)->map(function ($order) {
                    $days = abs($order->days_until_expected);
                    return "{$order->part_name} ({$order->job->job_number}) - {$days} days overdue";
                })->implode("\n");
                
                if ($overdueOrders->count() > 3) {
                    $details .= "\n+" . ($overdueOrders->count() - 3) . " more...";
                }
                
                Notification::notify(
                    $user->id,
                    Notification::TYPE_REMINDER,
                    $message,
                    $details,
                    route('part-orders.index', ['filter' => 'overdue']),
                    'exclamation-triangle-fill',
                    'danger'
                );
                
                $notificationsSent++;
            }
            
            $this->info("Notified {$sparepartUsers->count()} user(s) about {$overdueOrders->count()} overdue order(s)");
        }

        if ($dueSoonOrders->isEmpty() && $overdueOrders->isEmpty()) {
            $this->info('No orders due soon or overdue. No notifications sent.');
        }

        return Command::SUCCESS;
    }
}
