@extends('layouts.app')

@section('title', 'Data Tracker')

@section('content')
<div class="page-header">
    <h1><i class="bi bi-search-heart me-2"></i>Data Tracker</h1>
    <p class="text-muted">Search anything: plate number, WIP, VIN, customer name — see complete history</p>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group input-group-lg">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" class="form-control" 
                           placeholder="Enter plate number, WIP, VIN, or customer name..." 
                           value="{{ $query }}" autofocus>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-arrow-right-circle me-1"></i>Track
                    </button>
                </div>
            </div>
            @if($query)
            <div class="col-md-4 text-end">
                <a href="{{ route('tracker.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

@if($query && $detectedType)
<!-- Detected Type & Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Detected As</h6>
                <div>
                    @foreach($detectedType as $type)
                        @php
                            $badges = [
                                'plate_number' => ['Plate Number', 'primary'],
                                'wip' => ['WIP/Job Number', 'success'],
                                'vin' => ['VIN/Chassis', 'info'],
                                'customer' => ['Customer Name', 'warning'],
                                'booking' => ['Booking', 'secondary'],
                                'general' => ['General Search', 'dark'],
                            ];
                            $badge = $badges[$type] ?? ['Unknown', 'secondary'];
                        @endphp
                        <span class="badge bg-{{ $badge[1] }} fs-6">{{ $badge[0] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted mb-3">Found In</h6>
                <div class="row text-center">
                    <div class="col">
                        <h4 class="mb-0 text-primary">{{ $results['jobs'] }}</h4>
                        <small class="text-muted">Jobs</small>
                    </div>
                    <div class="col">
                        <h4 class="mb-0 text-info">{{ $results['vehicles'] }}</h4>
                        <small class="text-muted">Vehicles</small>
                    </div>
                    <div class="col">
                        <h4 class="mb-0 text-success">{{ $results['pdi_records'] }}</h4>
                        <small class="text-muted">PDI</small>
                    </div>
                    <div class="col">
                        <h4 class="mb-0 text-warning">{{ $results['bookings'] }}</h4>
                        <small class="text-muted">Bookings</small>
                    </div>
                    <div class="col">
                        <h4 class="mb-0 text-secondary">{{ $results['towing'] }}</h4>
                        <small class="text-muted">Towing</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Timeline -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Timeline</span>
        <span class="badge bg-secondary">{{ $timeline->count() }} events</span>
    </div>
    <div class="card-body p-0">
        @if($timeline->count() > 0)
        <div class="timeline-container" style="max-height: 600px; overflow-y: auto;">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light sticky-top">
                    <tr>
                        <th style="width: 140px;">Date</th>
                        <th style="width: 120px;">Type</th>
                        <th>Event</th>
                        <th>Details</th>
                        <th style="width: 100px;">Status</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeline as $event)
                    <tr>
                        <td class="text-nowrap">
                            <small>{{ \Carbon\Carbon::parse($event['date'])->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            @php
                                $typeColors = [
                                    'job' => 'primary',
                                    'job_date' => 'info',
                                    'invoiced' => 'success',
                                    'vehicle' => 'info',
                                    'pdi' => 'success',
                                    'booking' => 'warning',
                                    'towing' => 'secondary',
                                    'remark' => 'dark',
                                    'audit' => 'light',
                                ];
                                $color = $typeColors[$event['type']] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ ucfirst($event['type']) }}</span>
                        </td>
                        <td class="fw-bold">{{ $event['event'] }}</td>
                        <td>
                            <small>{{ $event['description'] }}</small>
                            @if(isset($event['user']))
                                <br><small class="text-muted">by {{ $event['user'] }}</small>
                            @endif
                        </td>
                        <td>
                            @if($event['status'] == 'invoiced')
                                <span class="badge bg-success">Invoiced</span>
                            @elseif($event['status'] == 'uninvoiced')
                                <span class="badge bg-warning text-dark">Uninvoiced</span>
                            @elseif($event['status'] == 'in_workshop')
                                <span class="badge bg-info">In Workshop</span>
                            @elseif($event['status'] == 'pending')
                                <span class="badge bg-secondary">Pending</span>
                            @elseif($event['status'] == 'completed')
                                <span class="badge bg-success">Completed</span>
                            @elseif($event['status'] == 'comment')
                                <span class="badge bg-dark">Comment</span>
                            @elseif($event['status'])
                                <span class="badge bg-secondary">{{ ucfirst($event['status']) }}</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($event['import']) && $event['import'])
                                <small class="text-muted" title="{{ $event['import']->file_name }}">
                                    <i class="bi bi-file-earmark-spreadsheet"></i>
                                    {{ Str::limit($event['import']->file_name, 20) }}
                                </small>
                            @elseif($event['type'] == 'audit')
                                <small class="text-muted">Audit Log</small>
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center text-muted py-5">
            <i class="bi bi-inbox display-4"></i>
            <p class="mt-2">No events found for "{{ $query }}"</p>
        </div>
        @endif
    </div>
</div>
@elseif(!$query)
<!-- Empty State -->
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-search display-1 text-muted"></i>
        <h4 class="mt-3">Track Any Data</h4>
        <p class="text-muted">
            Enter a plate number, WIP, VIN, or customer name above.<br>
            The system will automatically detect what it is and show the complete history.
        </p>
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <span class="badge bg-primary">Plate Number</span>
                    <span class="badge bg-success">WIP Number</span>
                    <span class="badge bg-info">VIN/Chassis</span>
                    <span class="badge bg-warning text-dark">Customer Name</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
