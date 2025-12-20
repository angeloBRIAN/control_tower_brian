<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobInvoice;
use App\Models\Booking;
use App\Models\PdiRecord;
use App\Models\TowingRecord;
use App\Models\Vehicle;
use App\Models\Remark;
use App\Models\Import;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataCleanupController extends Controller
{
    /**
     * Show the data cleanup confirmation page
     */
    public function index()
    {
        // Get counts for display
        $counts = [
            'jobs' => Job::count(),
            'job_invoices' => JobInvoice::count(),
            'bookings' => Booking::count(),
            'pdi_records' => PdiRecord::count(),
            'towing_records' => TowingRecord::count(),
            'vehicles' => Vehicle::count(),
            'remarks' => Remark::count(),
            'imports' => Import::count(),
            'audit_logs' => AuditLog::count(),
        ];

        $totalRecords = array_sum($counts);

        return view('admin.data-cleanup.index', compact('counts', 'totalRecords'));
    }

    /**
     * Execute the data cleanup
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'confirmation' => 'required|in:DELETE ALL DATA',
            'tables' => 'required|array|min:1',
        ]);

        $tablesToClean = $request->input('tables', []);
        $results = [];

        try {
            // Disable foreign key checks temporarily
            // Note: TRUNCATE auto-commits so we can't use transactions with it
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Clean selected tables
            if (in_array('remarks', $tablesToClean)) {
                $results['remarks'] = Remark::count();
                DB::table('remarks')->truncate();
            }

            if (in_array('job_invoices', $tablesToClean)) {
                $results['job_invoices'] = JobInvoice::count();
                DB::table('job_invoices')->truncate();
            }

            if (in_array('jobs', $tablesToClean)) {
                $results['jobs'] = Job::count();
                DB::table('jobs')->truncate();
            }

            if (in_array('bookings', $tablesToClean)) {
                $results['bookings'] = Booking::count();
                DB::table('bookings')->truncate();
            }

            if (in_array('pdi_records', $tablesToClean)) {
                $results['pdi_records'] = PdiRecord::count();
                DB::table('pdi_records')->truncate();
            }

            if (in_array('towing_records', $tablesToClean)) {
                $results['towing_records'] = TowingRecord::count();
                DB::table('towing_records')->truncate();
            }

            if (in_array('vehicles', $tablesToClean)) {
                $results['vehicles'] = Vehicle::count();
                DB::table('vehicles')->truncate();
            }

            if (in_array('imports', $tablesToClean)) {
                $results['imports'] = Import::count();
                DB::table('imports')->truncate();
            }

            if (in_array('audit_logs', $tablesToClean)) {
                $results['audit_logs'] = AuditLog::count();
                DB::table('audit_logs')->truncate();
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Log the cleanup action
            Log::info('Data cleanup executed by ' . auth()->user()->name, [
                'tables' => $tablesToClean,
                'records_deleted' => $results,
            ]);

            $totalDeleted = array_sum($results);
            return redirect()->route('admin.data-cleanup.index')
                ->with('success', "Data cleanup completed! Deleted {$totalDeleted} records from " . count($tablesToClean) . " table(s).");

        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            Log::error('Data cleanup failed: ' . $e->getMessage());
            
            return redirect()->route('admin.data-cleanup.index')
                ->with('error', 'Data cleanup failed: ' . $e->getMessage());
        }
    }
}
