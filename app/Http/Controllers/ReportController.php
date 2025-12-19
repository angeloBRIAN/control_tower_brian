<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\SavedReport;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReportController extends Controller
{
    // ===== EXISTING REPORT METHODS =====
    
    public function uninvoiced(Request $request)
    {
        $query = Job::with('vehicle')
            ->uninvoiced()
            ->latest('job_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('latest_remark', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('job_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('job_date', '<=', $request->date_to);
        }

        $jobs = $query->paginate(20);

        return view('reports.uninvoiced', compact('jobs'));
    }

    public function invoiced(Request $request)
    {
        $query = Job::with('vehicle')
            ->invoiced()
            ->latest('invoiced_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoiced_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoiced_at', '<=', $request->date_to);
        }

        $jobs = $query->paginate(20);

        return view('reports.invoiced', compact('jobs'));
    }

    public function needsParts(Request $request)
    {
        $query = Job::with(['vehicle', 'remarks'])
            ->uninvoiced()
            ->needsParts()
            ->latest('job_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_number', 'like', "%{$search}%")
                  ->orWhere('plate_number', 'like', "%{$search}%");
            });
        }

        $jobs = $query->paginate(20);

        return view('reports.needs_parts', compact('jobs'));
    }

    public function customerMerges(Request $request)
    {
        $query = \App\Models\CustomerMergeLog::orderBy('created_at', 'desc');

        // Filter by source type
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('old_name', 'like', "%{$search}%")
                  ->orWhere('canonical_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        // Stats
        $stats = [
            'total' => \App\Models\CustomerMergeLog::count(),
            'dms_issues' => \App\Models\CustomerMergeLog::where('source_type', 'dms_import')->count(),
            'user_mistakes' => \App\Models\CustomerMergeLog::whereIn('source_type', ['job_progress_import', 'user_entry'])->count(),
            'jobs_fixed' => \App\Models\CustomerMergeLog::sum('jobs_updated'),
            'vehicles_fixed' => \App\Models\CustomerMergeLog::sum('vehicles_updated'),
        ];

        return view('reports.customer_merges', compact('logs', 'stats'));
    }

    public function exportCustomerMerges(Request $request)
    {
        $query = \App\Models\CustomerMergeLog::orderBy('created_at', 'desc');

        // Apply same filters as main view
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('old_name', 'like', "%{$search}%")
                  ->orWhere('canonical_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->get();
        $format = $request->input('format', 'xlsx');

        // Source type labels
        $sourceLabels = [
            'dms_import' => 'DMS Import',
            'job_progress_import' => 'Job Progress',
            'user_entry' => 'Manual Entry',
        ];

        if ($format === 'pdf') {
            // PDF Export using simple HTML table
            $html = '<html><head><style>
                body { font-family: Arial, sans-serif; font-size: 10px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                th { background: #333; color: white; }
                .dms { background: #fee2e2; }
                .user { background: #fef3c7; }
                h1 { font-size: 16px; }
            </style></head><body>';
            $html .= '<h1>Customer Merge Report - ' . now()->format('d/m/Y H:i') . '</h1>';
            $html .= '<table><tr><th>Date</th><th>Old Name</th><th>Merged To</th><th>Source</th><th>Jobs</th><th>Vehicles</th><th>By</th></tr>';
            
            foreach ($logs as $log) {
                $rowClass = $log->source_type === 'dms_import' ? 'dms' : 'user';
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td>' . $log->created_at->format('d/m/Y H:i') . '</td>';
                $html .= '<td>' . e($log->old_name) . '</td>';
                $html .= '<td>' . e($log->canonical_name) . '</td>';
                $html .= '<td>' . ($sourceLabels[$log->source_type] ?? $log->source_type) . '</td>';
                $html .= '<td>' . $log->jobs_updated . '</td>';
                $html .= '<td>' . $log->vehicles_updated . '</td>';
                $html .= '<td>' . e($log->merged_by) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table></body></html>';

            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="customer_merges_' . date('Y-m-d') . '.pdf"',
            ]);
        }

        // Excel/CSV Export
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Date', 'Old Name', 'Merged To', 'Source', 'Jobs Updated', 'Vehicles Updated', 'Merged By', 'Notes'];
        $sheet->fromArray($headers, null, 'A1');

        // Style headers
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($logs as $log) {
            $sheet->fromArray([
                $log->created_at->format('d/m/Y H:i'),
                $log->old_name,
                $log->canonical_name,
                $sourceLabels[$log->source_type] ?? $log->source_type,
                $log->jobs_updated,
                $log->vehicles_updated,
                $log->merged_by,
                $log->notes,
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if ($format === 'csv') {
            $filename = 'customer_merges_' . date('Y-m-d_His') . '.csv';
            $writer = new Csv($spreadsheet);
            $contentType = 'text/csv';
        } else {
            $filename = 'customer_merges_' . date('Y-m-d_His') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function exportUninvoiced(Request $request)
    {
        $jobs = Job::with('vehicle')
            ->uninvoiced()
            ->latest('job_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Job Number', 'Plate Number', 'Service Advisor', 'Technician', 'Job Date', 'Promise Date', 'Amount', 'Work Status', 'Latest Remark', 'Last Updated'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($jobs as $index => $job) {
            $sheet->fromArray([
                $index + 1,
                $job->job_number,
                $job->plate_number,
                $job->service_advisor,
                $job->technician,
                $job->job_date?->format('d/m/Y'),
                $job->promise_date?->format('d/m/Y'),
                $job->estimated_amount,
                $job->work_status,
                $job->latest_remark,
                $job->latest_remark_at?->format('d/m/Y H:i'),
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'uninvoiced_jobs_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportNeedsParts(Request $request)
    {
        $jobs = Job::with(['vehicle', 'remarks'])
            ->uninvoiced()
            ->needsParts()
            ->latest('job_date')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Job Number', 'Plate Number', 'Service Advisor', 'Job Date', 'Latest Remark', 'Last Updated'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($jobs as $index => $job) {
            $sheet->fromArray([
                $index + 1,
                $job->job_number,
                $job->plate_number,
                $job->service_advisor,
                $job->job_date?->format('d/m/Y'),
                $job->latest_remark,
                $job->latest_remark_at?->format('d/m/Y H:i'),
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'needs_parts_jobs_' . date('Y-m-d_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ===== CUSTOM REPORT BUILDER =====

    /**
     * Available columns for job reports
     */
    private function getJobColumns(): array
    {
        return [
            'job_number' => ['label' => 'WIP', 'group' => 'Core'],
            'job_card' => ['label' => 'Job Card', 'group' => 'Core'],
            'franchise' => ['label' => 'Franchise', 'group' => 'Core'],
            'department' => ['label' => 'Department', 'group' => 'Core'],
            'job_date' => ['label' => 'Job Date', 'group' => 'Dates', 'type' => 'date'],
            'date_in' => ['label' => 'Date In', 'group' => 'Dates', 'type' => 'date'],
            'date_out' => ['label' => 'Date Out', 'group' => 'Dates', 'type' => 'date'],
            'check_in_time' => ['label' => 'Check-In Time', 'group' => 'Dates'],
            'promise_date' => ['label' => 'Promise Date', 'group' => 'Dates', 'type' => 'date'],
            'deadline' => ['label' => 'Deadline', 'group' => 'Dates', 'type' => 'date'],
            'plate_number' => ['label' => 'Reg No', 'group' => 'Vehicle'],
            'chassis_number' => ['label' => 'Chassis', 'group' => 'Vehicle'],
            'unit_type' => ['label' => 'Unit Type', 'group' => 'Vehicle'],
            'customer_name' => ['label' => 'Customer Name', 'group' => 'Customer'],
            'customer_address' => ['label' => 'Customer Address', 'group' => 'Customer'],
            'service_advisor' => ['label' => 'Service Advisor', 'group' => 'Personnel'],
            'foreman' => ['label' => 'Foreman', 'group' => 'Personnel'],
            'technician' => ['label' => 'Technician', 'group' => 'Personnel'],
            'block' => ['label' => 'Block', 'group' => 'Personnel'],
            'job_type' => ['label' => 'Job Type', 'group' => 'Job Info'],
            'payment_type' => ['label' => 'Payment Type', 'group' => 'Job Info'],
            'work_status' => ['label' => 'Work Status', 'group' => 'Job Info'],
            'labour_sales' => ['label' => 'Labour Sales', 'group' => 'Sales', 'type' => 'number'],
            'part_sales' => ['label' => 'Part Sales', 'group' => 'Sales', 'type' => 'number'],
            'total_sales' => ['label' => 'Total Sales', 'group' => 'Sales', 'type' => 'number'],
            'estimated_amount' => ['label' => 'Estimated', 'group' => 'Sales', 'type' => 'number'],
            'rq' => ['label' => 'RQ', 'group' => 'Parts'],
            'no_order_part_mbina' => ['label' => 'Order Part', 'group' => 'Parts'],
            'need_part' => ['label' => 'Needs Parts', 'group' => 'Parts', 'type' => 'boolean'],
            'status' => ['label' => 'Status', 'group' => 'Invoice'],
            'invoice_number' => ['label' => 'Invoice No', 'group' => 'Invoice'],
            'invoice_date' => ['label' => 'Inv Date', 'group' => 'Invoice', 'type' => 'date'],
            'inv_amount' => ['label' => 'Inv Amount', 'group' => 'Invoice', 'type' => 'number'],
            'latest_remark' => ['label' => 'Latest Remark', 'group' => 'Remarks'],
            'latest_remark_at' => ['label' => 'Remark Updated', 'group' => 'Remarks', 'type' => 'datetime'],
        ];
    }

    private function getFilterOptions(): array
    {
        return [
            'franchise' => ['PC', 'CV'],
            'status' => ['uninvoiced', 'invoiced'],
            'service_advisor' => Job::whereNotNull('service_advisor')->distinct()->pluck('service_advisor')->sort()->values()->toArray(),
            'foreman' => Job::whereNotNull('foreman')->distinct()->pluck('foreman')->sort()->values()->toArray(),
            'department' => Job::whereNotNull('department')->distinct()->pluck('department')->sort()->values()->toArray(),
            'work_status' => Job::whereNotNull('work_status')->distinct()->pluck('work_status')->sort()->values()->toArray(),
        ];
    }

    public function builder()
    {
        $columns = $this->getJobColumns();
        $filterOptions = $this->getFilterOptions();
        $savedReports = SavedReport::where('user_id', auth()->id())->orderBy('name')->get();
        
        $groupedColumns = [];
        foreach ($columns as $key => $col) {
            $group = $col['group'] ?? 'Other';
            $groupedColumns[$group][$key] = $col;
        }
        
        return view('reports.builder', compact('groupedColumns', 'filterOptions', 'savedReports'));
    }

    public function preview(Request $request)
    {
        $data = $this->buildQuery($request);
        $columns = $request->input('columns', []);
        $allColumns = $this->getJobColumns();
        $selectedColumns = array_intersect_key($allColumns, array_flip($columns));
        
        return response()->json([
            'success' => true,
            'columns' => $selectedColumns,
            'data' => $data->take(50)->get()->map(function ($job) use ($selectedColumns) {
                $row = [];
                foreach ($selectedColumns as $key => $col) {
                    $value = $job->{$key};
                    if (isset($col['type'])) {
                        if ($col['type'] === 'date' && $value) $value = $value->format('d/m/Y');
                        elseif ($col['type'] === 'datetime' && $value) $value = $value->format('d/m/Y H:i');
                        elseif ($col['type'] === 'number' && $value) $value = number_format($value, 0, ',', '.');
                        elseif ($col['type'] === 'boolean') $value = $value ? 'Yes' : 'No';
                    }
                    $row[$key] = $value ?? '';
                }
                return $row;
            }),
            'total' => $data->count(),
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');
        $columns = $request->input('columns', []);
        $allColumns = $this->getJobColumns();
        $selectedColumns = array_intersect_key($allColumns, array_flip($columns));
        $data = $this->buildQuery($request)->get();
        
        // Handle PDF/Print format
        if ($format === 'pdf' || $format === 'print') {
            $title = $request->input('title', 'Job Report');
            $titleAlign = $request->input('title_align', 'center');
            $orientation = $request->input('orientation', 'portrait');
            $header = $request->input('header', '');
            $footer = $request->input('footer', 'Page {page} of {pages}');
            
            // Process variables in header/footer
            $variables = [
                '{title}' => $title,
                '{date}' => now()->format('d/m/Y'),
                '{time}' => now()->format('H:i'),
                '{page}' => '', // Will be replaced by CSS/browser
                '{pages}' => '', // Will be replaced by CSS/browser
            ];
            
            $processedHeader = str_replace(array_keys($variables), array_values($variables), $header);
            $processedFooter = str_replace(array_keys($variables), array_values($variables), $footer);
            
            // Build applied filters description
            $appliedFilters = collect([
                $request->franchise ? "Franchise: {$request->franchise}" : null,
                $request->status ? "Status: {$request->status}" : null,
                $request->service_advisor ? "SA: {$request->service_advisor}" : null,
                $request->date_from ? "From: {$request->date_from}" : null,
                $request->date_to ? "To: {$request->date_to}" : null,
            ])->filter()->implode(' | ');
            
            return view('reports.print', [
                'columns' => $selectedColumns,
                'data' => $data,
                'title' => $title,
                'titleAlign' => $titleAlign,
                'orientation' => $orientation,
                'header' => $header,
                'footer' => $footer,
                'processedHeader' => $processedHeader,
                'processedFooter' => $processedFooter,
                'appliedFilters' => $appliedFilters,
            ]);
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $col = 1;
        foreach ($selectedColumns as $colDef) {
            $sheet->setCellValueByColumnAndRow($col++, 1, $colDef['label']);
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        
        // Data
        $row = 2;
        foreach ($data as $job) {
            $col = 1;
            foreach ($selectedColumns as $key => $colDef) {
                $value = $job->{$key};
                if (isset($colDef['type'])) {
                    if ($colDef['type'] === 'date' && $value) $value = $value->format('Y-m-d');
                    elseif ($colDef['type'] === 'datetime' && $value) $value = $value->format('Y-m-d H:i');
                    elseif ($colDef['type'] === 'boolean') $value = $value ? 'Yes' : 'No';
                }
                $sheet->setCellValueByColumnAndRow($col++, $row, $value ?? '');
            }
            $row++;
        }
        
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        $filename = 'job_report_' . now()->format('Ymd_His');
        
        if ($format === 'csv') {
            $writer = new Csv($spreadsheet);
            $filename .= '.csv';
            $contentType = 'text/csv';
        } else {
            $writer = new Xlsx($spreadsheet);
            $filename .= '.xlsx';
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }
        
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, ['Content-Type' => $contentType]);
    }

    public function saveReport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'columns' => 'required|array|min:1',
            'filters' => 'nullable|array',
        ]);
        
        $report = SavedReport::create([
            'name' => $validated['name'],
            'user_id' => auth()->id(),
            'data_source' => 'jobs',
            'columns' => $validated['columns'],
            'filters' => $validated['filters'] ?? [],
        ]);
        
        return response()->json(['success' => true, 'report' => $report]);
    }

    public function loadReport(SavedReport $report)
    {
        if ($report->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }
        return response()->json(['success' => true, 'report' => $report]);
    }

    public function deleteReport(SavedReport $report)
    {
        if ($report->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403);
        }
        $report->delete();
        return response()->json(['success' => true]);
    }

    private function buildQuery(Request $request)
    {
        $query = Job::query();
        
        if ($request->filled('date_from')) $query->whereDate('job_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('job_date', '<=', $request->date_to);
        
        foreach (['franchise', 'status', 'service_advisor', 'foreman', 'department', 'work_status'] as $field) {
            if ($request->filled($field)) $query->where($field, $request->input($field));
        }
        
        if ($request->filled('need_part')) {
            $query->where('need_part', $request->need_part === '1');
        }
        
        return $query->orderBy('job_date', 'desc');
    }
}

