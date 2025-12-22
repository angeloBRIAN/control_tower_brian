@component('mail::message')
# Weekly Workshop Report

**Period:** {{ $periodStart }} - {{ $periodEnd }}

---

## Summary

@component('mail::table')
| Metric | Value |
|:-------|------:|
| New Jobs This Week | {{ $reportData['new_jobs'] }} |
| Jobs Invoiced | {{ $reportData['invoiced_jobs'] }} |
| Currently Uninvoiced | {{ $reportData['uninvoiced_count'] }} |
| Needs Parts | {{ $reportData['needs_parts_count'] }} |
| **Total Revenue** | **IDR {{ number_format($reportData['total_revenue'], 0, ',', '.') }}** |
@endcomponent

---

## Job Aging (Uninvoiced)

@component('mail::table')
| Age Range | Count |
|:----------|------:|
| Fresh (< 7 days) | {{ $reportData['aging']['fresh'] }} |
| Aging (7-14 days) | {{ $reportData['aging']['aging'] }} |
| Stale (> 14 days) | {{ $reportData['aging']['stale'] }} |
@endcomponent

@if($reportData['top_sas']->count() > 0)
---

## Top Service Advisors (by Revenue)

@component('mail::table')
| SA Name | Jobs | Revenue |
|:--------|-----:|--------:|
@foreach($reportData['top_sas'] as $sa)
| {{ $sa->service_advisor }} | {{ $sa->jobs }} | IDR {{ number_format($sa->revenue, 0, ',', '.') }} |
@endforeach
@endcomponent
@endif

---

@component('mail::button', ['url' => config('app.url'), 'color' => 'primary'])
Open Dashboard
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
