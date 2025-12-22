@extends('layouts.app')

@section('title', 'Report Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="bi bi-envelope-paper me-2"></i>Report Settings</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Reports Configuration -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i>Scheduled Reports
                </div>
                <div class="card-body">
                    @foreach($reports as $report)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">{{ $report->name }}</h5>
                                <small class="text-muted">{{ $report->description }}</small>
                            </div>
                            <form action="{{ route('admin.report-settings.update', $report) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="is_enabled" value="1" 
                                           {{ $report->is_enabled ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label">{{ $report->is_enabled ? 'Enabled' : 'Disabled' }}</label>
                                </div>
                            </form>
                        </div>

                        <!-- Schedule Settings -->
                        <form action="{{ route('admin.report-settings.update', $report) }}" method="POST" class="row g-2 mb-3">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_enabled" value="{{ $report->is_enabled ? 1 : 0 }}">
                            <div class="col-auto">
                                <select name="schedule" class="form-select form-select-sm">
                                    <option value="daily" {{ $report->schedule === 'daily' ? 'selected' : '' }}>Daily</option>
                                    <option value="weekly" {{ $report->schedule === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ $report->schedule === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="schedule_day" class="form-select form-select-sm">
                                    <option value="1" {{ $report->schedule_day === '1' ? 'selected' : '' }}>Monday</option>
                                    <option value="2" {{ $report->schedule_day === '2' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="3" {{ $report->schedule_day === '3' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="4" {{ $report->schedule_day === '4' ? 'selected' : '' }}>Thursday</option>
                                    <option value="5" {{ $report->schedule_day === '5' ? 'selected' : '' }}>Friday</option>
                                    <option value="6" {{ $report->schedule_day === '6' ? 'selected' : '' }}>Saturday</option>
                                    <option value="7" {{ $report->schedule_day === '7' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="time" name="schedule_time" class="form-control form-control-sm" value="{{ $report->schedule_time }}">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-save"></i> Save Schedule
                                </button>
                            </div>
                        </form>

                        <!-- Recipients -->
                        <div class="mt-3">
                            <h6 class="mb-2"><i class="bi bi-people me-1"></i>Recipients</h6>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @forelse($report->recipients_list as $email)
                                <span class="badge bg-light text-dark border d-flex align-items-center gap-1">
                                    {{ $email }}
                                    <form action="{{ route('admin.report-settings.remove-recipient', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="email" value="{{ $email }}">
                                        <button type="submit" class="btn-close btn-close-sm" style="font-size: 0.6rem;"></button>
                                    </form>
                                </span>
                                @empty
                                <span class="text-muted small">No recipients configured</span>
                                @endforelse
                            </div>
                            
                            <!-- Add Recipient -->
                            <form action="{{ route('admin.report-settings.add-recipient', $report) }}" method="POST" class="input-group input-group-sm" style="max-width: 400px;">
                                @csrf
                                <input type="email" name="email" class="form-control" placeholder="Add email..." required>
                                <button type="submit" class="btn btn-outline-success"><i class="bi bi-plus"></i></button>
                            </form>
                            
                            <!-- Quick Add from Users -->
                            @if($adminEmails->count() > 0)
                            <div class="mt-2">
                                <small class="text-muted">Quick add:</small>
                                @foreach($adminEmails as $name => $email)
                                    @if(!in_array($email, $report->recipients_list))
                                    <form action="{{ route('admin.report-settings.add-recipient', $report) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="email" value="{{ $email }}">
                                        <button type="submit" class="btn btn-sm btn-link p-0 mx-1">{{ $name }}</button>
                                    </form>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Send Now -->
                        <div class="mt-3 pt-3 border-top">
                            <form action="{{ route('admin.report-settings.send-now', $report) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" {{ count($report->recipients_list) === 0 ? 'disabled' : '' }}>
                                    <i class="bi bi-send me-1"></i>Send Now
                                </button>
                            </form>
                            <span class="text-muted small ms-2">{{ $report->schedule_description }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- SMTP Settings -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <i class="bi bi-hdd-network me-2"></i>SMTP Settings
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.report-settings.smtp') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-8">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" name="host" class="form-control" value="{{ $smtp?->host }}" placeholder="smtp.gmail.com" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label">Port</label>
                                <input type="number" name="port" class="form-control" value="{{ $smtp?->port ?? 587 }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="{{ $smtp?->username }}" placeholder="your@email.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" placeholder="{{ $smtp?->password ? '••••••••' : 'Enter password' }}">
                                <small class="text-muted">Leave blank to keep existing password</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Encryption</label>
                                <select name="encryption" class="form-select">
                                    <option value="tls" {{ ($smtp?->encryption ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ $smtp?->encryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ $smtp?->encryption === null ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">From Address</label>
                                <input type="email" name="from_address" class="form-control" value="{{ $smtp?->from_address }}" placeholder="noreply@example.com" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">From Name</label>
                                <input type="text" name="from_name" class="form-control" value="{{ $smtp?->from_name ?? 'Control Tower' }}" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Save SMTP Settings
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Test SMTP -->
                    <hr>
                    <h6><i class="bi bi-lightning me-1"></i>Test Connection</h6>
                    <form action="{{ route('admin.report-settings.test-smtp') }}" method="POST" class="input-group">
                        @csrf
                        <input type="email" name="test_email" class="form-control" placeholder="test@email.com" required>
                        <button type="submit" class="btn btn-outline-success">Send Test</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
