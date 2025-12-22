<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReport;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:weekly 
                            {--email= : Send to a specific email address}
                            {--preview : Preview the report without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly workshop report to managers and admins';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating weekly report...');

        // Preview mode - render the email to console
        if ($this->option('preview')) {
            $mail = new WeeklyReport();
            $this->info('Report Data:');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['New Jobs', $mail->reportData['new_jobs']],
                    ['Invoiced Jobs', $mail->reportData['invoiced_jobs']],
                    ['Uninvoiced', $mail->reportData['uninvoiced_count']],
                    ['Needs Parts', $mail->reportData['needs_parts_count']],
                    ['Revenue', number_format($mail->reportData['total_revenue'], 0, ',', '.')],
                ]
            );
            $this->info("Report period: {$mail->periodStart} - {$mail->periodEnd}");
            return Command::SUCCESS;
        }

        // Determine recipients
        $recipients = [];

        if ($email = $this->option('email')) {
            $recipients = [$email];
            $this->info("Sending to: {$email}");
        } else {
            // Send to all admins and managers
            $recipients = User::whereIn('role', ['admin', 'manager'])
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();
            
            $this->info('Sending to ' . count($recipients) . ' admin/manager recipients');
        }

        if (empty($recipients)) {
            $this->warn('No recipients found. Use --email to specify a recipient.');
            return Command::FAILURE;
        }

        // Send the report
        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new WeeklyReport());
                $this->info("✓ Sent to: {$email}");
            } catch (\Exception $e) {
                $this->error("✗ Failed to send to {$email}: {$e->getMessage()}");
            }
        }

        $this->info('Weekly report sent successfully!');

        return Command::SUCCESS;
    }
}
