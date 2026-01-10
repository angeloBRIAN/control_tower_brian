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
    public function kanban()
    {
        // Define columns - regular statuses + Credit Notes
        $statuses = JobInvoice::STATUSES;
        
        // Fetch all non-cancelled invoices
        $invoices = JobInvoice::with('job')
            ->where('status', '!=', JobInvoice::STATUS_CANCELLED)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Fetch credit notes separately
        $creditNotes = JobInvoice::with('job')
            ->where('invoice_type', 'credit_note')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Group by status
        $invoicesByStatus = [];
        foreach (array_keys($statuses) as $status) {
            $invoicesByStatus[$status] = $invoices->where('status', $status)
                ->where('invoice_type', 'invoice');
        }
        
        // Add Credit Notes as separate column
        $invoicesByStatus['credit_note'] = $creditNotes;

        return view('finance.kanban', compact('statuses', 'invoicesByStatus', 'creditNotes'));
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
