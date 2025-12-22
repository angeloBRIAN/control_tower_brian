<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReport extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $reportData;
    public string $periodStart;
    public string $periodEnd;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->periodEnd = now()->format('d M Y');
        $this->periodStart = now()->subDays(7)->format('d M Y');
        $this->reportData = $this->generateReportData();
    }

    /**
     * Generate the weekly report data.
     */
    protected function generateReportData(): array
    {
        $weekAgo = now()->subDays(7);
        
        // Jobs summary
        $newJobs = Job::where('created_at', '>=', $weekAgo)->count();
        $invoicedJobs = Job::where('invoiced_at', '>=', $weekAgo)->count();
        $currentUninvoiced = Job::uninvoiced()->count();
        $currentNeedsParts = Job::uninvoiced()->needsParts()->count();

        // Revenue summary
        $totalRevenue = Job::where('invoiced_at', '>=', $weekAgo)
            ->sum('total_sales') ?? 0;

        // Top Service Advisors
        $topSAs = Job::where('invoiced_at', '>=', $weekAgo)
            ->selectRaw('service_advisor, SUM(total_sales) as revenue, COUNT(*) as jobs')
            ->whereNotNull('service_advisor')
            ->groupBy('service_advisor')
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // Aging breakdown (uninvoiced)
        $today = now()->startOfDay();
        $aging = [
            'fresh' => Job::uninvoiced()->where('job_date', '>', $today->copy()->subDays(7))->count(),
            'aging' => Job::uninvoiced()->whereBetween('job_date', [$today->copy()->subDays(14), $today->copy()->subDays(7)])->count(),
            'stale' => Job::uninvoiced()->where('job_date', '<', $today->copy()->subDays(14))->count(),
        ];

        return [
            'new_jobs' => $newJobs,
            'invoiced_jobs' => $invoicedJobs,
            'uninvoiced_count' => $currentUninvoiced,
            'needs_parts_count' => $currentNeedsParts,
            'total_revenue' => $totalRevenue,
            'top_sas' => $topSAs,
            'aging' => $aging,
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Weekly Workshop Report ({$this->periodStart} - {$this->periodEnd})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.weekly',
            with: [
                'reportData' => $this->reportData,
                'periodStart' => $this->periodStart,
                'periodEnd' => $this->periodEnd,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
