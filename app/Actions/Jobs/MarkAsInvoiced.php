<?php

namespace App\Actions\Jobs;

use App\Models\Job;
use App\Models\Notification;

class MarkAsInvoiced
{
    /**
     * Mark a job as invoiced.
     *
     * @param Job $job The job to mark as invoiced
     * @param string $invoiceNumber The invoice number
     * @param string $remark Mandatory remark for the action
     * @param string|null $userName Name of user performing the action
     * @param int|null $userId ID of user performing the action
     * @return Job
     */
    public function execute(Job $job, string $invoiceNumber, string $remark, ?string $userName = null, ?int $userId = null): Job
    {
        // Mark the job as invoiced
        $job->markAsInvoiced($invoiceNumber);

        // Add the mandatory remark
        $fullRemark = "Marked as Invoiced (Inv: {$invoiceNumber}): {$remark}";
        $job->addRemark($fullRemark, $userName, $userId);

        // Notify relevant users
        $this->notifyStakeholders($job, $invoiceNumber);

        return $job;
    }

    /**
     * Notify stakeholders about the invoiced job.
     */
    protected function notifyStakeholders(Job $job, string $invoiceNumber): void
    {
        // Notify control_tower and admin users
        Notification::notifyRole(
            'control_tower',
            Notification::TYPE_SYSTEM,
            "Job {$job->job_number} Invoiced",
            "Invoice #{$invoiceNumber} created for {$job->plate_number}",
            route('jobs.show', $job->id),
            'receipt',
            'success'
        );
    }
}
