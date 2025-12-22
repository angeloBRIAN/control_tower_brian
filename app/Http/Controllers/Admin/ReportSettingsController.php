<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportSetting;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReportSettingsController extends Controller
{
    /**
     * Show report settings page.
     */
    public function index()
    {
        $reports = ReportSetting::all();
        $smtp = SmtpSetting::first();
        $adminEmails = User::whereIn('role', ['admin', 'manager'])->pluck('email', 'name')->filter();
        
        return view('admin.report-settings.index', compact('reports', 'smtp', 'adminEmails'));
    }

    /**
     * Update report settings.
     */
    public function updateReport(Request $request, ReportSetting $report)
    {
        $validated = $request->validate([
            'is_enabled' => 'boolean',
            'schedule' => 'required|in:daily,weekly,monthly',
            'schedule_time' => 'required|date_format:H:i',
            'schedule_day' => 'nullable|string',
        ]);

        $report->update($validated);

        return back()->with('success', "Report '{$report->name}' updated successfully.");
    }

    /**
     * Add recipient to a report.
     */
    public function addRecipient(Request $request, ReportSetting $report)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $report->addRecipient($validated['email']);

        return back()->with('success', "Recipient added to '{$report->name}'.");
    }

    /**
     * Remove recipient from a report.
     */
    public function removeRecipient(Request $request, ReportSetting $report)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $report->removeRecipient($validated['email']);

        return back()->with('success', "Recipient removed from '{$report->name}'.");
    }

    /**
     * Update SMTP settings.
     */
    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'required|in:tls,ssl,none',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        // Handle encryption 'none' as null
        if ($validated['encryption'] === 'none') {
            $validated['encryption'] = null;
        }

        $smtp = SmtpSetting::first();
        
        if ($smtp) {
            // Only update password if provided
            if (empty($validated['password'])) {
                unset($validated['password']);
            }
            $smtp->update($validated);
        } else {
            SmtpSetting::create($validated);
        }

        return back()->with('success', 'SMTP settings updated successfully.');
    }

    /**
     * Test SMTP connection.
     */
    public function testSmtp(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email',
        ]);

        $smtp = SmtpSetting::getActive();
        
        if (!$smtp) {
            return back()->with('error', 'No SMTP settings configured.');
        }

        try {
            // Apply SMTP settings
            $smtp->applyToMailer();

            // Send test email
            Mail::raw('This is a test email from Control Tower.', function ($message) use ($validated) {
                $message->to($validated['test_email'])
                    ->subject('Control Tower - SMTP Test');
            });

            return back()->with('success', "Test email sent to {$validated['test_email']}!");
        } catch (\Exception $e) {
            return back()->with('error', 'SMTP test failed: ' . $e->getMessage());
        }
    }

    /**
     * Send a report manually.
     */
    public function sendNow(ReportSetting $report)
    {
        if ($report->recipients_list === [] || count($report->recipients_list) === 0) {
            return back()->with('error', 'No recipients configured for this report.');
        }

        try {
            \Artisan::call('report:weekly');
            return back()->with('success', "Report sent to " . count($report->recipients_list) . " recipients.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send report: ' . $e->getMessage());
        }
    }
}
