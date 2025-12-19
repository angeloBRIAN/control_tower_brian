<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\PdiRecord;
use App\Models\TowingRecord;
use App\Models\AuditLog;
use App\Models\Remark;
use App\Models\Import;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TrackerController extends Controller
{
    /**
     * Display the tracker search page.
     */
    public function index(Request $request)
    {
        $query = $request->input('q');
        $results = null;
        $detectedType = null;
        $timeline = collect();

        if ($query && strlen($query) >= 2) {
            // Detect what type of data this might be
            $detectedType = $this->detectType($query);
            
            // Build timeline based on detected type
            $timeline = $this->buildTimeline($query, $detectedType);
            
            // Get summary results
            $results = $this->getResults($query, $detectedType);
        }

        return view('tracker.index', [
            'query' => $query,
            'detectedType' => $detectedType,
            'timeline' => $timeline,
            'results' => $results,
        ]);
    }

    /**
     * Detect what type of data the query might be.
     */
    private function detectType(string $query): array
    {
        $types = [];
        $query = trim($query);

        // Check if it matches plate number pattern (letters + numbers)
        if (preg_match('/^[A-Z]{1,2}\s?\d{1,4}\s?[A-Z]{0,3}$/i', $query)) {
            $types[] = 'plate_number';
        }

        // Check in vehicles
        if (Vehicle::where('plate_number', 'like', "%{$query}%")->exists()) {
            $types[] = 'plate_number';
        }

        // Check in jobs by job_number (WIP)
        if (Job::where('job_number', 'like', "%{$query}%")->exists()) {
            $types[] = 'wip';
        }

        // Check in jobs by plate_number
        if (Job::where('plate_number', 'like', "%{$query}%")->exists()) {
            if (!in_array('plate_number', $types)) {
                $types[] = 'plate_number';
            }
        }

        // Check in PDI by VIN
        if (PdiRecord::where('vin', 'like', "%{$query}%")->exists()) {
            $types[] = 'vin';
        }

        // Check in PDI by WIP
        if (PdiRecord::where('wip', 'like', "%{$query}%")->exists()) {
            if (!in_array('wip', $types)) {
                $types[] = 'wip';
            }
        }

        // Check in bookings
        if (Booking::where('plate_number', 'like', "%{$query}%")->orWhere('wip', 'like', "%{$query}%")->exists()) {
            if (!in_array('plate_number', $types) && !in_array('wip', $types)) {
                $types[] = 'booking';
            }
        }

        // Check in towing
        if (TowingRecord::where('plate_number', 'like', "%{$query}%")->exists()) {
            if (!in_array('plate_number', $types)) {
                $types[] = 'plate_number';
            }
        }

        // Check customer names
        if (Job::where('customer_name', 'like', "%{$query}%")->exists() ||
            Vehicle::where('customer_name', 'like', "%{$query}%")->exists()) {
            $types[] = 'customer';
        }

        // If no specific type found, mark as general search
        if (empty($types)) {
            $types[] = 'general';
        }

        return array_unique($types);
    }

    /**
     * Build a timeline of all events related to the query.
     */
    private function buildTimeline(string $query, array $types): Collection
    {
        $events = collect();

        // Search in all relevant tables and build timeline

        // Jobs
        $jobs = Job::where('job_number', 'like', "%{$query}%")
            ->orWhere('plate_number', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        foreach ($jobs as $job) {
            $events->push([
                'date' => $job->created_at,
                'type' => 'job',
                'event' => 'Job Created',
                'description' => "WIP: {$job->job_number} | Plate: {$job->plate_number}",
                'status' => $job->status,
                'model' => $job,
                'import' => $job->import,
            ]);

            // Add job date if different from created
            if ($job->job_date && $job->job_date != $job->created_at?->toDateString()) {
                $events->push([
                    'date' => $job->job_date,
                    'type' => 'job_date',
                    'event' => 'Job Date',
                    'description' => "WIP: {$job->job_number} scheduled",
                    'status' => $job->status,
                    'model' => $job,
                ]);
            }

            // Add invoiced date if applicable
            if ($job->invoiced_at) {
                $events->push([
                    'date' => $job->invoiced_at,
                    'type' => 'invoiced',
                    'event' => 'Invoiced',
                    'description' => "WIP: {$job->job_number} invoiced",
                    'status' => 'invoiced',
                    'model' => $job,
                ]);
            }
        }

        // Vehicles
        $vehicles = Vehicle::where('plate_number', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($vehicles as $vehicle) {
            $events->push([
                'date' => $vehicle->created_at,
                'type' => 'vehicle',
                'event' => 'Vehicle Registered',
                'description' => "Plate: {$vehicle->plate_number} | Model: {$vehicle->model}",
                'status' => $vehicle->is_in_workshop ? 'in_workshop' : 'out',
                'model' => $vehicle,
                'import' => $vehicle->import,
            ]);
        }

        // PDI Records
        $pdiRecords = PdiRecord::where('vin', 'like', "%{$query}%")
            ->orWhere('wip', 'like', "%{$query}%")
            ->orWhere('plate_number', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($pdiRecords as $pdi) {
            $events->push([
                'date' => $pdi->pdi_date ?? $pdi->created_at,
                'type' => 'pdi',
                'event' => 'PDI Record',
                'description' => "VIN: {$pdi->vin} | Model: {$pdi->model}",
                'status' => $pdi->status,
                'model' => $pdi,
                'import' => $pdi->import,
            ]);
        }

        // Bookings
        $bookings = Booking::where('plate_number', 'like', "%{$query}%")
            ->orWhere('wip', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($bookings as $booking) {
            $events->push([
                'date' => $booking->booking_date ?? $booking->created_at,
                'type' => 'booking',
                'event' => 'Booking',
                'description' => "Plate: {$booking->plate_number} | Customer: {$booking->customer_name}",
                'status' => $booking->status,
                'model' => $booking,
                'import' => $booking->import,
            ]);
        }

        // Towing Records
        $towingRecords = TowingRecord::where('plate_number', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($towingRecords as $towing) {
            $events->push([
                'date' => $towing->scheduled_date ?? $towing->created_at,
                'type' => 'towing',
                'event' => 'Towing',
                'description' => "Plate: {$towing->plate_number} | Location: {$towing->pickup_location}",
                'status' => $towing->status,
                'model' => $towing,
                'import' => $towing->import,
            ]);
        }

        // Remarks related to jobs matching query
        $jobIds = $jobs->pluck('id')->toArray();
        if (!empty($jobIds)) {
            $remarks = Remark::whereIn('job_id', $jobIds)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->limit(30)
                ->get();

            foreach ($remarks as $remark) {
                $events->push([
                    'date' => $remark->created_at,
                    'type' => 'remark',
                    'event' => 'Remark Added',
                    'description' => \Str::limit($remark->remark, 80),
                    'status' => 'comment',
                    'model' => $remark,
                    'user' => $remark->user?->name ?? 'System',
                ]);
            }
        }

        // Audit logs for related records
        $auditLogs = AuditLog::where(function($q) use ($query) {
            $q->where('old_values', 'like', "%{$query}%")
              ->orWhere('new_values', 'like', "%{$query}%");
        })
        ->orderBy('created_at', 'desc')
        ->limit(30)
        ->get();

        foreach ($auditLogs as $log) {
            $events->push([
                'date' => $log->created_at,
                'type' => 'audit',
                'event' => ucfirst($log->event) . ' ' . class_basename($log->auditable_type),
                'description' => "By: " . ($log->user?->name ?? 'System'),
                'status' => $log->event,
                'model' => $log,
            ]);
        }

        // Sort by date descending
        return $events->sortByDesc('date')->values();
    }

    /**
     * Get summary results for display.
     */
    private function getResults(string $query, array $types): array
    {
        return [
            'jobs' => Job::where('job_number', 'like', "%{$query}%")
                ->orWhere('plate_number', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->count(),
            'vehicles' => Vehicle::where('plate_number', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->count(),
            'pdi_records' => PdiRecord::where('vin', 'like', "%{$query}%")
                ->orWhere('wip', 'like', "%{$query}%")
                ->orWhere('plate_number', 'like', "%{$query}%")
                ->count(),
            'bookings' => Booking::where('plate_number', 'like', "%{$query}%")
                ->orWhere('wip', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->count(),
            'towing' => TowingRecord::where('plate_number', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->count(),
        ];
    }
}
