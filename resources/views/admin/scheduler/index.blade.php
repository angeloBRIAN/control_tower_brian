@extends('layouts.app')

@section('title', 'Scheduler Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-clock-history me-2"></i>Scheduler Management</h1>
        <p class="text-muted mb-0">View and manage scheduled background tasks</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Task Name</th>
                    <th>Schedule</th>
                    <th>Description</th>
                    <th>Next Run</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schedules as $schedule)
                <tr>
                    <td>
                        <strong>{{ $schedule['name'] }}</strong>
                        <br>
                        <code class="small text-muted">{{ $schedule['command'] }}</code>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $schedule['schedule'] }}</span>
                    </td>
                    <td>
                        <small>{{ $schedule['description'] }}</small>
                    </td>
                    <td>
                        <i class="bi bi-calendar-event me-1"></i>
                        {{ $schedule['next_run'] }}
                    </td>
                    <td>
                        <form action="{{ route('admin.scheduler.run') }}" method="POST" class="d-inline" 
                              onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm\'></span>';">
                            @csrf
                            <input type="hidden" name="command" value="{{ $schedule['command'] }}">
                            <button type="submit" class="btn btn-sm btn-success" title="Run Now">
                                <i class="bi bi-play-fill"></i> Run Now
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-info-circle me-1"></i>About Scheduler
    </div>
    <div class="card-body">
        <p class="mb-2">These tasks run automatically in the background based on their schedule.</p>
        <ul class="mb-3">
            <li><strong>Weekly Report</strong> - Sends summary email to admins every Monday</li>
            <li><strong>Customer Duplicate Scan</strong> - Recalculates duplicate customer groups for fast loading</li>
            <li><strong>Scheduled Email Reports</strong> - Checks and sends configured email reports</li>
        </ul>
        
        <div class="alert alert-info mb-0">
            <i class="bi bi-lightbulb me-2"></i>
            <strong>Note:</strong> For the scheduler to work, you must have this cron job running on your server:
            <pre class="mt-2 mb-0 bg-dark text-light p-2 rounded"><code>* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1</code></pre>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="bi bi-terminal me-1"></i>Quick Commands
    </div>
    <div class="card-body">
        <p class="mb-3">Run these commands manually if needed:</p>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="border rounded p-3">
                    <h6><i class="bi bi-people me-1"></i>Scan Duplicates</h6>
                    <code class="small d-block mb-2">php artisan customers:find-duplicates</code>
                    <form action="{{ route('admin.scheduler.run') }}" method="POST">
                        @csrf
                        <input type="hidden" name="command" value="customers:find-duplicates">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-play-fill"></i> Run
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3">
                    <h6><i class="bi bi-envelope me-1"></i>Send Reports</h6>
                    <code class="small d-block mb-2">php artisan reports:send</code>
                    <form action="{{ route('admin.scheduler.run') }}" method="POST">
                        @csrf
                        <input type="hidden" name="command" value="reports:send">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-play-fill"></i> Run
                        </button>
                    </form>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3">
                    <h6><i class="bi bi-file-earmark-text me-1"></i>Weekly Report</h6>
                    <code class="small d-block mb-2">php artisan report:weekly</code>
                    <form action="{{ route('admin.scheduler.run') }}" method="POST">
                        @csrf
                        <input type="hidden" name="command" value="report:weekly">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-play-fill"></i> Run
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
