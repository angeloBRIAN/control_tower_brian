@extends('layouts.app')

@section('title', 'Trends & Comparisons')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="bi bi-graph-up-arrow me-2"></i>Trends & Comparisons</h1>
        <p class="text-muted mb-0">Analyze performance trends and spot patterns</p>
    </div>
    <div class="btn-group" role="group">
        <a href="?period=week" class="btn btn-{{ $period === 'week' ? 'primary' : 'outline-primary' }}">Week</a>
        <a href="?period=month" class="btn btn-{{ $period === 'month' ? 'primary' : 'outline-primary' }}">Month</a>
        <a href="?period=quarter" class="btn btn-{{ $period === 'quarter' ? 'primary' : 'outline-primary' }}">Quarter</a>
    </div>
</div>

<!-- Period Comparison Header -->
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="bi bi-calendar-range fs-4 me-3"></i>
    <div>
        <strong>{{ $periodData['label'] }}</strong><br>
        <small>{{ $periodData['currentPeriod'] }} vs {{ $periodData['previousPeriod'] }}</small>
    </div>
</div>

<!-- Period Comparison Cards -->
<div class="row g-4 mb-4">
    <!-- New Jobs -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">New Jobs</h6>
                        <h2 class="mb-0">{{ number_format($periodData['metrics']['new_jobs']['current']) }}</h2>
                    </div>
                    <span class="badge bg-{{ $periodData['metrics']['new_jobs']['change']['direction'] === 'up' ? 'success' : ($periodData['metrics']['new_jobs']['change']['direction'] === 'down' ? 'danger' : 'secondary') }} fs-6">
                        @if($periodData['metrics']['new_jobs']['change']['direction'] === 'up')
                            <i class="bi bi-arrow-up"></i>
                        @elseif($periodData['metrics']['new_jobs']['change']['direction'] === 'down')
                            <i class="bi bi-arrow-down"></i>
                        @endif
                        {{ $periodData['metrics']['new_jobs']['change']['value'] }}%
                    </span>
                </div>
                <small class="text-muted">Previous: {{ number_format($periodData['metrics']['new_jobs']['previous']) }}</small>
            </div>
        </div>
    </div>

    <!-- Invoiced -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">Invoiced</h6>
                        <h2 class="mb-0">{{ number_format($periodData['metrics']['invoiced']['current']) }}</h2>
                    </div>
                    <span class="badge bg-{{ $periodData['metrics']['invoiced']['change']['direction'] === 'up' ? 'success' : ($periodData['metrics']['invoiced']['change']['direction'] === 'down' ? 'danger' : 'secondary') }} fs-6">
                        @if($periodData['metrics']['invoiced']['change']['direction'] === 'up')
                            <i class="bi bi-arrow-up"></i>
                        @elseif($periodData['metrics']['invoiced']['change']['direction'] === 'down')
                            <i class="bi bi-arrow-down"></i>
                        @endif
                        {{ $periodData['metrics']['invoiced']['change']['value'] }}%
                    </span>
                </div>
                <small class="text-muted">Previous: {{ number_format($periodData['metrics']['invoiced']['previous']) }}</small>
            </div>
        </div>
    </div>

    <!-- Revenue -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">Revenue</h6>
                        <h2 class="mb-0">Rp {{ number_format($periodData['metrics']['revenue']['current'] / 1000000, 1) }}M</h2>
                    </div>
                    <span class="badge bg-{{ $periodData['metrics']['revenue']['change']['direction'] === 'up' ? 'success' : ($periodData['metrics']['revenue']['change']['direction'] === 'down' ? 'danger' : 'secondary') }} fs-6">
                        @if($periodData['metrics']['revenue']['change']['direction'] === 'up')
                            <i class="bi bi-arrow-up"></i>
                        @elseif($periodData['metrics']['revenue']['change']['direction'] === 'down')
                            <i class="bi bi-arrow-down"></i>
                        @endif
                        {{ $periodData['metrics']['revenue']['change']['value'] }}%
                    </span>
                </div>
                <small class="text-muted">Previous: Rp {{ number_format($periodData['metrics']['revenue']['previous'] / 1000000, 1) }}M</small>
            </div>
        </div>
    </div>

    <!-- Avg Days to Invoice -->
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-1">Avg Days to Invoice</h6>
                        <h2 class="mb-0">{{ $periodData['metrics']['avg_days']['current'] }}</h2>
                    </div>
                    <span class="badge bg-{{ $periodData['metrics']['avg_days']['change']['direction'] === 'up' ? 'success' : ($periodData['metrics']['avg_days']['change']['direction'] === 'down' ? 'danger' : 'secondary') }} fs-6">
                        @if($periodData['metrics']['avg_days']['change']['direction'] === 'up')
                            <i class="bi bi-arrow-up"></i>
                        @elseif($periodData['metrics']['avg_days']['change']['direction'] === 'down')
                            <i class="bi bi-arrow-down"></i>
                        @endif
                        {{ $periodData['metrics']['avg_days']['change']['value'] }}%
                    </span>
                </div>
                <small class="text-muted">Previous: {{ $periodData['metrics']['avg_days']['previous'] }} days</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- SA Performance Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>SA Close Rate Trend (6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="saPerformanceChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Aging Trend -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Aging Backlog Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="agingChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Franchise Comparison -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Franchise Comparison (This Month)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="franchiseChart" height="200"></canvas>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-center"><span class="badge bg-primary">PC</span></th>
                                    <th class="text-center"><span class="badge bg-warning text-dark">CV</span></th>
                                    <th class="text-center">Diff</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total Jobs</td>
                                    <td class="text-center fw-bold">{{ $franchiseData['pc']['total'] }}</td>
                                    <td class="text-center fw-bold">{{ $franchiseData['cv']['total'] }}</td>
                                    <td class="text-center text-muted">
                                        @if($franchiseData['pc']['total'] > $franchiseData['cv']['total'])
                                            PC +{{ $franchiseData['pc']['total'] - $franchiseData['cv']['total'] }}
                                        @else
                                            CV +{{ $franchiseData['cv']['total'] - $franchiseData['pc']['total'] }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Invoiced</td>
                                    <td class="text-center fw-bold text-success">{{ $franchiseData['pc']['invoiced'] }}</td>
                                    <td class="text-center fw-bold text-success">{{ $franchiseData['cv']['invoiced'] }}</td>
                                    <td class="text-center text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>Avg Days to Invoice</td>
                                    <td class="text-center">{{ $franchiseData['pc']['avg_days'] }}</td>
                                    <td class="text-center">{{ $franchiseData['cv']['avg_days'] }}</td>
                                    <td class="text-center">
                                        @if($franchiseData['pc']['avg_days'] < $franchiseData['cv']['avg_days'])
                                            <span class="text-success">PC faster</span>
                                        @else
                                            <span class="text-warning">CV faster</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Parts Pending %</td>
                                    <td class="text-center">{{ $franchiseData['pc']['parts_pct'] }}%</td>
                                    <td class="text-center">{{ $franchiseData['cv']['parts_pct'] }}%</td>
                                    <td class="text-center text-muted">-</td>
                                </tr>
                                <tr>
                                    <td>Total Revenue</td>
                                    <td class="text-center">Rp {{ number_format($franchiseData['pc']['revenue'] / 1000000, 1) }}M</td>
                                    <td class="text-center">Rp {{ number_format($franchiseData['cv']['revenue'] / 1000000, 1) }}M</td>
                                    <td class="text-center">-</td>
                                </tr>
                                <tr>
                                    <td>Avg Job Value</td>
                                    <td class="text-center">Rp {{ number_format($franchiseData['pc']['avg_value'] / 1000000, 2) }}M</td>
                                    <td class="text-center">Rp {{ number_format($franchiseData['cv']['avg_value'] / 1000000, 2) }}M</td>
                                    <td class="text-center">
                                        @if($franchiseData['cv']['avg_value'] > 0 && $franchiseData['pc']['avg_value'] > 0)
                                            {{ number_format($franchiseData['cv']['avg_value'] / $franchiseData['pc']['avg_value'], 1) }}x
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Color palette
    const colors = [
        'rgba(0, 161, 170, 1)',    // Teal
        'rgba(102, 16, 242, 1)',   // Indigo
        'rgba(253, 126, 20, 1)',   // Orange
        'rgba(25, 135, 84, 1)',    // Green
        'rgba(220, 53, 69, 1)',    // Red
    ];
    
    // SA Performance Chart
    const saCtx = document.getElementById('saPerformanceChart').getContext('2d');
    const saLabels = @json($saPerformance['labels']);
    const saDatasets = @json($saPerformance['datasets']);
    
    const datasets = Object.entries(saDatasets).map(([name, data], index) => ({
        label: name,
        data: data,
        borderColor: colors[index % colors.length],
        backgroundColor: colors[index % colors.length].replace('1)', '0.1)'),
        tension: 0.3,
        fill: false,
        pointRadius: 4,
        pointHoverRadius: 6,
    }));
    
    new Chart(saCtx, {
        type: 'line',
        data: {
            labels: saLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + (context.raw ?? 'N/A') + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Close Rate %'
                    }
                }
            }
        }
    });
    
    // Aging Trend Chart
    const agingCtx = document.getElementById('agingChart').getContext('2d');
    const agingLabels = @json($agingData['labels']);
    const agingRawData = @json($agingData['data']);
    
    new Chart(agingCtx, {
        type: 'bar',
        data: {
            labels: agingLabels,
            datasets: [
                {
                    label: '> 30 days',
                    data: agingRawData.map(d => d.over30),
                    backgroundColor: 'rgba(220, 53, 69, 0.8)',
                },
                {
                    label: '> 14 days',
                    data: agingRawData.map(d => d.over14 - d.over30),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                },
                {
                    label: '> 7 days',
                    data: agingRawData.map(d => d.over7 - d.over14),
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Jobs'
                    }
                }
            }
        }
    });
    
    // Franchise Comparison Chart
    const franchiseCtx = document.getElementById('franchiseChart').getContext('2d');
    
    new Chart(franchiseCtx, {
        type: 'bar',
        data: {
            labels: ['Total Jobs', 'Invoiced', 'Parts Pending'],
            datasets: [
                {
                    label: 'PC',
                    data: [
                        {{ $franchiseData['pc']['total'] }},
                        {{ $franchiseData['pc']['invoiced'] }},
                        {{ $franchiseData['pc']['parts_pending'] }}
                    ],
                    backgroundColor: 'rgba(13, 110, 253, 0.8)',
                },
                {
                    label: 'CV',
                    data: [
                        {{ $franchiseData['cv']['total'] }},
                        {{ $franchiseData['cv']['invoiced'] }},
                        {{ $franchiseData['cv']['parts_pending'] }}
                    ],
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection
