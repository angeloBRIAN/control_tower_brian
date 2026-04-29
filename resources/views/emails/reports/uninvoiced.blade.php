<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportTitle }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; background: #f5f5f5; }
        .email-container { max-width: 700px; margin: 0 auto; background: #fff; }
        .header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; font-weight: 600; margin-bottom: 5px; }
        .header .date { opacity: 0.9; font-size: 14px; }
        .content { padding: 25px; }
        
        /* Summary Cards */
        .summary-grid { display: table; width: 100%; margin-bottom: 25px; }
        .summary-card { display: table-cell; width: 33.33%; padding: 8px; vertical-align: top; }
        .summary-card-inner { border-radius: 12px; padding: 20px; text-align: center; }
        .summary-card.total .summary-card-inner { background: linear-gradient(135deg, rgba(220, 53, 69, 0.12), rgba(220, 53, 69, 0.2)); border: 1px solid rgba(220, 53, 69, 0.3); }
        .summary-card.pc .summary-card-inner { background: linear-gradient(135deg, rgba(0, 123, 255, 0.12), rgba(0, 123, 255, 0.2)); border: 1px solid rgba(0, 123, 255, 0.3); }
        .summary-card.cv .summary-card-inner { background: linear-gradient(135deg, rgba(255, 193, 7, 0.12), rgba(255, 193, 7, 0.2)); border: 1px solid rgba(255, 193, 7, 0.3); }
        .summary-value { font-size: 28px; font-weight: 700; }
        .summary-card.total .summary-value { color: #dc3545; }
        .summary-card.pc .summary-value { color: #007bff; }
        .summary-card.cv .summary-value { color: #e0a800; }
        .summary-label { font-size: 12px; color: #666; margin-top: 5px; }
        .summary-sublabel { font-size: 11px; color: #888; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #ddd; }
        
        /* Breakdown Section */
        .section { margin-bottom: 25px; }
        .section-header { background: #f8f9fa; padding: 12px 15px; border-radius: 8px 8px 0 0; border-bottom: 2px solid #e9ecef; display: flex; justify-content: space-between; align-items: center; }
        .section-title { font-weight: 600; font-size: 14px; color: #495057; }
        .section-badge { background: #dc3545; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 500; }
        
        /* Work Status Grid */
        .status-grid { display: table; width: 100%; background: #fff; border: 1px solid #e9ecef; border-top: none; border-radius: 0 0 8px 8px; }
        .status-row { display: table-row; }
        .status-cell { display: table-cell; padding: 12px; text-align: center; border-right: 1px solid #e9ecef; width: 16.66%; }
        .status-cell:last-child { border-right: none; }
        .status-count { font-size: 20px; font-weight: 700; color: #495057; }
        .status-name { font-size: 10px; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        
        /* Table */
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; }
        table.data-table th { background: #343a40; color: white; padding: 12px 10px; text-align: left; font-weight: 500; }
        table.data-table th.amount { text-align: right; }
        table.data-table td { padding: 10px; border-bottom: 1px solid #e9ecef; }
        table.data-table td.amount { text-align: right; font-family: 'Courier New', monospace; }
        table.data-table tr:nth-child(even) { background: #f8f9fa; }
        table.data-table tr:hover { background: #e9ecef; }
        
        /* SA & Franchise Breakdown */
        .breakdown-grid { display: table; width: 100%; }
        .breakdown-col { display: table-cell; width: 50%; padding: 8px; vertical-align: top; }
        .breakdown-box { border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden; }
        .breakdown-header { padding: 10px 12px; font-weight: 600; font-size: 13px; display: flex; justify-content: space-between; align-items: center; }
        .breakdown-header.franchise { background: linear-gradient(90deg, rgba(0, 123, 255, 0.1), transparent); }
        .breakdown-header.sa { background: linear-gradient(90deg, rgba(40, 167, 69, 0.1), transparent); }
        .breakdown-item { padding: 8px 12px; border-top: 1px solid #e9ecef; display: flex; justify-content: space-between; font-size: 12px; }
        .breakdown-item:nth-child(even) { background: #f8f9fa; }
        
        /* Filters Applied */
        .filters-applied { background: #e7f3ff; border: 1px solid #b6d4fe; border-radius: 8px; padding: 12px 15px; margin-bottom: 20px; font-size: 12px; }
        .filters-applied strong { color: #0d6efd; }
        
        /* Footer */
        .footer { background: #f8f9fa; padding: 20px; text-align: center; border-top: 1px solid #e9ecef; }
        .footer p { font-size: 12px; color: #6c757d; margin: 3px 0; }
        .footer a { color: #0d6efd; text-decoration: none; }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .summary-grid, .summary-card, .breakdown-grid, .breakdown-col { display: block; width: 100%; }
            .summary-card { margin-bottom: 10px; padding: 5px 0; }
            .breakdown-col { margin-bottom: 15px; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $reportTitle }}</h1>
            <div class="date">{{ $reportDate }}</div>
        </div>
        
        <div class="content">
            @if(!empty($appliedFilters))
            <div class="filters-applied">
                <strong>Filters Applied:</strong>
                @foreach($appliedFilters as $filter => $value)
                    {{ ucfirst(str_replace('_', ' ', $filter)) }}: {{ $value }}{{ !$loop->last ? ' • ' : '' }}
                @endforeach
            </div>
            @endif
            
            <!-- Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card total">
                    <div class="summary-card-inner">
                        <div class="summary-value">{{ number_format($totalJobs) }}</div>
                        <div class="summary-label">Total Uninvoiced Jobs</div>
                        <div class="summary-sublabel">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="summary-card pc">
                    <div class="summary-card-inner">
                        <div class="summary-value">{{ number_format($pcJobs ?? 0) }}</div>
                        <div class="summary-label">PC - Passenger Car</div>
                        <div class="summary-sublabel">Rp {{ number_format($pcAmount ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="summary-card cv">
                    <div class="summary-card-inner">
                        <div class="summary-value">{{ number_format($cvJobs ?? 0) }}</div>
                        <div class="summary-label">CV - Commercial Vehicle</div>
                        <div class="summary-sublabel">Rp {{ number_format($cvAmount ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            @if(isset($workStatusBreakdown) && count($workStatusBreakdown) > 0)
            <!-- Work Status Breakdown -->
            <div class="section">
                <div class="section-header">
                    <span class="section-title">📊 Work Status Breakdown</span>
                    <span class="section-badge">{{ $totalJobs }} jobs</span>
                </div>
                <div class="status-grid">
                    <div class="status-row">
                        @foreach($workStatusBreakdown->take(6) as $status)
                        <div class="status-cell">
                            <div class="status-count">{{ number_format($status['count']) }}</div>
                            <div class="status-name">{{ Str::limit($status['name'], 10) }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Franchise & SA Breakdown -->
            <div class="breakdown-grid">
                @if(isset($byFranchise) && $byFranchise->isNotEmpty())
                <div class="breakdown-col">
                    <div class="breakdown-box">
                        <div class="breakdown-header franchise">
                            <span>By Franchise</span>
                        </div>
                        @foreach($byFranchise as $franchise => $data)
                        <div class="breakdown-item">
                            <span>{{ $franchise ?: 'Unassigned' }}</span>
                            <span><strong>{{ $data['count'] }}</strong> jobs • Rp {{ number_format($data['amount'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($bySA) && $bySA->isNotEmpty())
                <div class="breakdown-col">
                    <div class="breakdown-box">
                        <div class="breakdown-header sa">
                            <span>Top Service Advisors</span>
                        </div>
                        @foreach($bySA->take(5) as $sa => $data)
                        <div class="breakdown-item">
                            <span>{{ $sa ?: 'Unassigned' }}</span>
                            <span><strong>{{ $data['count'] }}</strong> jobs • Rp {{ number_format($data['amount'], 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            @if(isset($jobs) && $jobs->isNotEmpty())
            <!-- Top Jobs Table -->
            <div class="section">
                <div class="section-header">
                    <span class="section-title">📋 Recent Uninvoiced Jobs</span>
                    <span class="section-badge">Showing {{ $jobs->count() }} of {{ $totalJobs }}</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>WIP</th>
                            <th>Plate</th>
                            <th>SA</th>
                            <th>Date</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobs->take(15) as $job)
                        <tr>
                            <td><strong>{{ $job->job_number }}</strong></td>
                            <td>{{ $job->plate_number }}</td>
                            <td>{{ $job->service_advisor ?? '-' }}</td>
                            <td>{{ $job->job_date?->format('d/m/Y') }}</td>
                            <td>{{ Str::limit($job->customer_address, 20) ?? '-' }}</td>
                            <td>{{ $job->work_status ?? 'Pending' }}</td>
                            <td class="amount">{{ $job->total_sales ? number_format($job->total_sales, 0, ',', '.') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($totalJobs > 15)
                <div style="text-align: center; padding: 15px; color: #6c757d; font-size: 12px;">
                    + {{ $totalJobs - 15 }} more jobs not shown. View full report in Control Tower.
                </div>
                @endif
            </div>
            @endif
        </div>
        
        <div class="footer">
            <p>This is an automated report from <strong>Control Tower</strong></p>
            <p><a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
        </div>
    </div>
</body>
</html>
