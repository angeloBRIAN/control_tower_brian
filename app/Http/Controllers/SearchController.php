<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Vehicle;
use App\Models\Customer;
use App\Models\JobInvoice;
use App\Models\PartOrder;
use App\Models\Booking;
use App\Models\TowingRecord;
use App\Models\PdiRecord;
use App\Models\ServiceAdvisor;
use App\Models\Foreman;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across all major entities
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'message' => 'Please enter at least 2 characters'
            ]);
        }
        
        $results = [];
        
        // Search Jobs
        $jobs = Job::where('job_number', 'like', "%{$query}%")
            ->orWhere('plate_number', 'like', "%{$query}%")
            ->orWhere('invoice_number', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orWhere('chassis_number', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'job_number', 'plate_number', 'customer_name', 'status']);
            
        foreach ($jobs as $job) {
            $results[] = [
                'type' => 'job',
                'icon' => 'bi-wrench',
                'title' => $job->job_number,
                'subtitle' => $job->plate_number . ' - ' . ($job->customer_name ?? 'No customer'),
                'badge' => $job->status === 'invoiced' ? 'Invoiced' : 'Uninvoiced',
                'badge_class' => $job->status === 'invoiced' ? 'bg-success' : 'bg-warning text-dark',
                'url' => route('jobs.show', $job->id),
            ];
        }
        
        // Search Vehicles
        $vehicles = Vehicle::where('plate_number', 'like', "%{$query}%")
            ->orWhere('vin', 'like', "%{$query}%")
            ->orWhere('customer_name', 'like', "%{$query}%")
            ->orWhere('model', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'plate_number', 'model', 'customer_name', 'is_in_workshop']);
            
        foreach ($vehicles as $vehicle) {
            $results[] = [
                'type' => 'vehicle',
                'icon' => 'bi-car-front',
                'title' => $vehicle->plate_number,
                'subtitle' => ($vehicle->model ?? 'Unknown') . ' - ' . ($vehicle->customer_name ?? 'No owner'),
                'badge' => $vehicle->is_in_workshop ? 'In Workshop' : null,
                'badge_class' => 'bg-info',
                'url' => route('vehicles.show', $vehicle->id),
            ];
        }
        
        // Search Job Invoices
        $invoices = JobInvoice::where('invoice_number', 'like', "%{$query}%")
            ->orWhereHas('job', function($q) use ($query) {
                $q->where('job_number', 'like', "%{$query}%")
                  ->orWhere('plate_number', 'like', "%{$query}%")
                  ->orWhere('customer_name', 'like', "%{$query}%");
            })
            ->with('job')
            ->limit(5)
            ->get();
            
        foreach ($invoices as $invoice) {
            $results[] = [
                'type' => 'invoice',
                'icon' => 'bi-receipt',
                'title' => $invoice->invoice_number ?: 'No Invoice #',
                'subtitle' => ($invoice->job ? $invoice->job->job_number . ' - ' . $invoice->job->customer_name : 'No Job'),
                'badge' => ucfirst(str_replace('_', ' ', $invoice->status)),
                'badge_class' => $invoice->status === 'paid' ? 'bg-success' : ($invoice->status === 'pending' ? 'bg-warning text-dark' : 'bg-info'),
                'url' => $invoice->job ? route('jobs.show', $invoice->job->id) : '#',
            ];
        }
        
        // Search Part Orders
        $partOrders = PartOrder::where('rq', 'like', "%{$query}%")
            ->orWhere('no_order_part', 'like', "%{$query}%")
            ->orWhereHas('job', function($q) use ($query) {
                $q->where('job_number', 'like', "%{$query}%")
                  ->orWhere('plate_number', 'like', "%{$query}%");
            })
            ->with('job')
            ->limit(5)
            ->get();
            
        foreach ($partOrders as $order) {
            $results[] = [
                'type' => 'part_order',
                'icon' => 'bi-box-seam',
                'title' => 'RQ: ' . ($order->rq ?: 'N/A'),
                'subtitle' => ($order->job ? $order->job->job_number . ' - ' . $order->job->plate_number : 'No Job'),
                'badge' => ucfirst(str_replace('_', ' ', $order->status)),
                'badge_class' => $order->status === 'received' ? 'bg-success' : 'bg-warning text-dark',
                'url' => $order->job ? route('jobs.show', $order->job->id) : route('part-orders.index'),
            ];
        }
        
        // Search Bookings
        if (auth()->user()->canManageMasterData()) {
            $bookings = Booking::where('plate_number', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->orWhere('customer_phone', 'like', "%{$query}%")
                ->limit(5)
                ->get();
                
            foreach ($bookings as $booking) {
                $results[] = [
                    'type' => 'booking',
                    'icon' => 'bi-calendar-check',
                    'title' => $booking->plate_number ?: 'No Plate',
                    'subtitle' => ($booking->customer_name ?: 'No Name') . ' - ' . $booking->booking_date . ' ' . $booking->booking_time,
                    'badge' => ucfirst($booking->status),
                    'badge_class' => $booking->status === 'completed' ? 'bg-success' : ($booking->status === 'confirmed' ? 'bg-info' : 'bg-warning text-dark'),
                    'url' => route('bookings.show', $booking->id),
                ];
            }
        }
        
        // Search Towing Records
        if (auth()->user()->canManageMasterData()) {
            $towingRecords = TowingRecord::where('plate_number', 'like', "%{$query}%")
                ->orWhere('customer_name', 'like', "%{$query}%")
                ->orWhere('customer_phone', 'like', "%{$query}%")
                ->orWhere('pickup_location', 'like', "%{$query}%")
                ->limit(5)
                ->get();
                
            foreach ($towingRecords as $record) {
                $results[] = [
                    'type' => 'towing',
                    'icon' => 'bi-truck',
                    'title' => $record->plate_number ?: 'No Plate',
                    'subtitle' => ($record->customer_name ?: 'No Name') . ' - ' . ($record->pickup_location ?: 'Unknown location'),
                    'badge' => 'Towing',
                    'badge_class' => 'bg-secondary',
                    'url' => route('towing-records.show', $record->id),
                ];
            }
        }
        
        // Search PDI Records
        if (auth()->user()->canManageMasterData()) {
            $pdiRecords = PdiRecord::where('plate_number', 'like', "%{$query}%")
                ->orWhere('vin', 'like', "%{$query}%")
                ->limit(5)
                ->get();
                
            foreach ($pdiRecords as $record) {
                $results[] = [
                    'type' => 'pdi',
                    'icon' => 'bi-clipboard-check',
                    'title' => $record->plate_number ?: 'No Plate',
                    'subtitle' => 'PDI Check - ' . ($record->vin ?: 'No VIN'),
                    'badge' => 'PDI',
                    'badge_class' => 'bg-primary',
                    'url' => route('pdi-records.show', $record->id),
                ];
            }
        }
        
        // Search by Customer Name in Customers table
        $customers = Customer::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($customers as $customer) {
            $jobCount = Job::where('customer_name', $customer->name)->count();
            $results[] = [
                'type' => 'customer',
                'icon' => 'bi-person',
                'title' => $customer->name,
                'subtitle' => $jobCount . ' job(s)' . ($customer->email ? ' - ' . $customer->email : ''),
                'badge' => null,
                'badge_class' => null,
                'url' => route('customers.show', $customer->id),
            ];
        }
        
        // Search Service Advisors
        $serviceAdvisors = ServiceAdvisor::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($serviceAdvisors as $sa) {
            $jobCount = Job::where('service_advisor', $sa->name)->count();
            $results[] = [
                'type' => 'service_advisor',
                'icon' => 'bi-person-badge',
                'title' => $sa->name,
                'subtitle' => 'Service Advisor - ' . $jobCount . ' job(s)',
                'badge' => 'SA',
                'badge_class' => 'bg-info',
                'url' => route('jobs.index', ['service_advisor' => $sa->name]),
            ];
        }
        
        // Search Foremen
        $foremen = Foreman::where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get();
            
        foreach ($foremen as $foreman) {
            $jobCount = Job::where('foreman', $foreman->name)->count();
            $results[] = [
                'type' => 'foreman',
                'icon' => 'bi-person-gear',
                'title' => $foreman->name,
                'subtitle' => 'Foreman - ' . $jobCount . ' job(s)',
                'badge' => 'Foreman',
                'badge_class' => 'bg-secondary',
                'url' => route('jobs.index', ['foreman' => $foreman->name]),
            ];
        }
        
        // Sort by relevance (exact matches first)
        usort($results, function($a, $b) use ($query) {
            $aExact = stripos($a['title'], $query) === 0 ? 0 : 1;
            $bExact = stripos($b['title'], $query) === 0 ? 0 : 1;
            return $aExact - $bExact;
        });
        
        return response()->json([
            'results' => array_slice($results, 0, 15), // Increased from 10 to 15
            'total' => count($results),
            'query' => $query
        ]);
    }
}
