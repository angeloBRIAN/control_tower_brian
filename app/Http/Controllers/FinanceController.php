<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobInvoice;
use App\Models\JobActivity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinanceController extends Controller
{
    /**
     * Display Finance Kanban board (Invoice-based)
     */
    public function kanban(Request $request)
    {
        // Define columns - regular statuses + Credit Notes
        $statuses = JobInvoice::STATUSES;
        
        // Build base query with job relationship
        $query = JobInvoice::with('job')
            ->join('jobs', 'job_invoices.job_id', '=', 'jobs.id')
            ->select('job_invoices.*');
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('job_invoices.invoice_number', 'like', "%{$search}%")
                  ->orWhere('jobs.job_number', 'like', "%{$search}%")
                  ->orWhere('jobs.plate_number', 'like', "%{$search}%")
                  ->orWhere('jobs.customer_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('service_advisor')) {
            $query->where('jobs.service_advisor', $request->input('service_advisor'));
        }
        
        if ($request->filled('foreman')) {
            $query->where('jobs.foreman', $request->input('foreman'));
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('job_invoices.invoice_date', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('job_invoices.invoice_date', '<=', $request->input('date_to'));
        }
        
        if ($request->filled('invoice_status')) {
            $query->where('job_invoices.status', $request->input('invoice_status'));
        }
        
        // Fetch all non-cancelled invoices
        $invoices = (clone $query)
            ->where('job_invoices.status', '!=', JobInvoice::STATUS_CANCELLED)
            ->orderBy('job_invoices.updated_at', 'desc')
            ->get();

        // Fetch credit notes separately
        $creditNotes = (clone $query)
            ->where('job_invoices.invoice_type', 'credit_note')
            ->orderBy('job_invoices.updated_at', 'desc')
            ->get();

        // Group by status
        $invoicesByStatus = [];
        foreach (array_keys($statuses) as $status) {
            $invoicesByStatus[$status] = $invoices->where('status', $status)
                ->where('invoice_type', 'invoice');
        }
        
        // Add Credit Notes as separate column
        $invoicesByStatus['credit_note'] = $creditNotes;
        
        // Get filter options for dropdowns
        $filterOptions = [
            'service_advisors' => Job::whereHas('invoices')
                ->distinct()
                ->whereNotNull('service_advisor')
                ->pluck('service_advisor')
                ->sort()
                ->values(),
            'foremen' => Job::whereHas('invoices')
                ->distinct()
                ->whereNotNull('foreman')
                ->pluck('foreman')
                ->sort()
                ->values(),
        ];

        return view('finance.kanban', compact('statuses', 'invoicesByStatus', 'creditNotes', 'filterOptions'));
    }

    /**
     * Update Invoice Status with Mandatory Remark
     * Logs to Job Activity Timeline and adds comment to Job
     */
    public function updateStatus(Request $request, JobInvoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(JobInvoice::STATUSES)),
            'remark' => 'required|string|min:3',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $oldStatus = $invoice->status;
        $newStatus = $validated['status'];
        $job = $invoice->job;

        // Update invoice
        $updateData = [
            'status' => $newStatus,
            'finance_remark' => $validated['remark'],
        ];

        // Handle paid amount if provided
        if (isset($validated['paid_amount'])) {
            $updateData['paid_amount'] = $validated['paid_amount'];
        }

        // Set paid_at when marked as paid
        if ($newStatus === JobInvoice::STATUS_PAID) {
            $updateData['paid_at'] = now();
            // If no paid_amount specified, use full amount
            if (!isset($validated['paid_amount'])) {
                $updateData['paid_amount'] = $invoice->inv_ppn_meterai;
            }
        }

        $invoice->update($updateData);

        // Sync job work_status based on invoice status
        if ($job) {
            $workStatusMap = [
                JobInvoice::STATUS_DRAFT => '11. Proses Invoice',
                JobInvoice::STATUS_PENDING => '12. Menunggu Pembayaran',
                JobInvoice::STATUS_PARTIALLY_PAID => '12. Menunggu Pembayaran',
                JobInvoice::STATUS_PAID => '13. Sudah Dibayar',
            ];
            
            if (isset($workStatusMap[$newStatus])) {
                $newWorkStatus = $workStatusMap[$newStatus];
                $oldWorkStatus = $job->work_status;
                
                if ($oldWorkStatus !== $newWorkStatus) {
                    $job->update(['work_status' => $newWorkStatus]);
                }
            }
        }

        // Log to Job Activity Timeline
        if ($job) {
            JobActivity::log(
                $job,
                'invoice_status_changed',
                "Invoice {$invoice->invoice_number}: {$oldStatus} → {$newStatus}",
                [
                    'invoice_id' => $invoice->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'amount' => $invoice->inv_ppn_meterai,
                ]
            );

            // Add remark to Job comments
            $job->addRemark(
                "📄 Invoice #{$invoice->invoice_number} ({$oldStatus} → {$newStatus}): " . $validated['remark'],
                auth()->user()->name,
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Invoice moved to " . JobInvoice::STATUSES[$newStatus],
            'invoice' => $invoice->fresh()->load('job'),
        ]);
    }

    /**
     * Record partial payment for an invoice
     */
    public function recordPayment(Request $request, JobInvoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'remark' => 'required|string|min:3',
        ]);

        $newPaidAmount = (float)$invoice->paid_amount + (float)$validated['amount'];
        $totalAmount = (float)$invoice->inv_ppn_meterai;

        // Determine new status based on payment
        if ($newPaidAmount >= $totalAmount) {
            $newStatus = JobInvoice::STATUS_PAID;
            $newPaidAmount = $totalAmount; // Cap at total
        } else {
            $newStatus = JobInvoice::STATUS_PARTIALLY_PAID;
        }

        $oldStatus = $invoice->status;

        $invoice->update([
            'status' => $newStatus,
            'paid_amount' => $newPaidAmount,
            'paid_at' => $newStatus === JobInvoice::STATUS_PAID ? now() : null,
            'finance_remark' => $validated['remark'],
        ]);

        // Log activity
        $job = $invoice->job;
        
        // Sync job work_status based on payment status
        if ($job) {
            $newWorkStatus = $newStatus === JobInvoice::STATUS_PAID 
                ? '13. Sudah Dibayar' 
                : '12. Menunggu Pembayaran';
            
            if ($job->work_status !== $newWorkStatus) {
                $job->update(['work_status' => $newWorkStatus]);
            }
        }
        
        if ($job) {
            JobActivity::log(
                $job,
                'invoice_payment_received',
                "Payment received for Invoice {$invoice->invoice_number}: " . number_format($validated['amount'], 0, ',', '.'),
                [
                    'invoice_id' => $invoice->id,
                    'payment_amount' => $validated['amount'],
                    'total_paid' => $newPaidAmount,
                    'remaining' => $totalAmount - $newPaidAmount,
                ]
            );

            $job->addRemark(
                "💰 Payment received for Invoice #{$invoice->invoice_number}: Rp " . number_format($validated['amount'], 0, ',', '.') .
                " (Total: Rp " . number_format($newPaidAmount, 0, ',', '.') . "/" . number_format($totalAmount, 0, ',', '.') . ")\n" .
                $validated['remark'],
                auth()->user()->name,
                auth()->id()
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Payment recorded. Invoice is now " . JobInvoice::STATUSES[$newStatus],
            'invoice' => $invoice->fresh()->load('job'),
        ]);
    }
}
