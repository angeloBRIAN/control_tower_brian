{{-- Widget: Week Calendar --}}
@props(['weekEvents' => []])

@php
    $startOfWeek = now()->startOfWeek();
    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $day = $startOfWeek->copy()->addDays($i);
        $days[] = [
            'date' => $day,
            'label' => $day->format('D'),
            'day' => $day->format('j'),
            'isToday' => $day->isToday(),
            'events' => collect($weekEvents)->filter(fn($e) => 
                isset($e['date']) && \Carbon\Carbon::parse($e['date'])->isSameDay($day)
            )->values()
        ];
    }
@endphp

<div class="card h-100">
    <div class="card-header-modern">
        <span class="card-header-title">
            <i class="bi bi-calendar-week text-primary"></i>This Week
        </span>
        <span class="badge bg-light text-dark">{{ now()->format('M Y') }}</span>
    </div>
    <div class="card-body p-2">
        <div class="row g-1 text-center mb-2">
            @foreach($days as $day)
            <div class="col">
                <div class="p-2 rounded {{ $day['isToday'] ? 'bg-primary text-white' : '' }}">
                    <small class="d-block {{ $day['isToday'] ? '' : 'text-muted' }}">{{ $day['label'] }}</small>
                    <strong>{{ $day['day'] }}</strong>
                    @if(count($day['events']) > 0)
                    <div class="mt-1">
                        <span class="badge {{ $day['isToday'] ? 'bg-white text-primary' : 'bg-primary' }}" style="font-size: 0.65rem;">
                            {{ count($day['events']) }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        {{-- Today's events --}}
        <div class="border-top pt-2 mt-2">
            <small class="text-muted d-block mb-2">Today's Schedule:</small>
            @php $todayEvents = collect($weekEvents)->filter(fn($e) => isset($e['date']) && \Carbon\Carbon::parse($e['date'])->isToday()); @endphp
            @forelse($todayEvents->take(3) as $event)
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-{{ $event['color'] ?? 'primary' }}" style="width: 8px; height: 8px; padding: 0; border-radius: 50%;"></span>
                <small class="text-truncate">{{ $event['title'] ?? 'Event' }}</small>
                @if(isset($event['time']))
                <small class="text-muted ms-auto">{{ $event['time'] }}</small>
                @endif
            </div>
            @empty
            <small class="text-muted">No events today</small>
            @endforelse
        </div>
    </div>
</div>
