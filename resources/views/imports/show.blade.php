@extends('layouts.app')

@section('title', 'Import ' . $import->original_name)

@section('content')
@php
    $status = \App\Enums\V1\ImportStatus::from($import->status);
    $percent = $import->total_rows > 0 ? min(100, (int) floor($import->processed_rows / $import->total_rows * 100)) : ($status->isFinished() ? 100 : 0);
@endphp

<div class="page-head">
    <div>
        <span class="eyebrow">CSV import</span>
        <h1>{{ $import->original_name }}</h1>
        <p class="flex wrap">
            @include('imports.partials.status-badge', ['status' => $import->status])
            <span class="faint">·</span>
            <span>Uploaded {{ \Illuminate\Support\Carbon::parse($import->created_at)->format('d M Y, H:i') }}</span>
            @if ($import->completed_at)
                <span class="faint">·</span>
                <span>Finished {{ \Illuminate\Support\Carbon::parse($import->completed_at)->format('d M Y, H:i') }}</span>
            @endif
        </p>
    </div>
    <div class="actions">
        <a href="{{ route('imports.index') }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
        @unless ($status->isFinished())
            <a href="{{ route('imports.show', $import->id) }}" class="btn"><i class="icon-refresh-cw"></i>Refresh</a>
        @endunless
    </div>
</div>

@if ($status === \App\Enums\V1\ImportStatus::Failed)
    <div class="callout red">
        <i class="icon-circle-alert"></i>
        <div><span class="strong">The import failed.</span> {{ $import->error_message }}</div>
    </div>
@elseif (! $status->isFinished())
    <div class="callout info">
        <i class="icon-hourglass"></i>
        <div>The import is running in the background. This page refreshes every few seconds; you can also leave it and come back later.</div>
    </div>
@endif

<div class="grid g-4">
    <div class="card stat"><i class="icon-file-spreadsheet"></i><div><div class="stat-label">Rows in file</div><div class="stat-value">{{ $import->total_rows }}</div></div></div>
    <div class="card stat"><i class="icon-loader"></i><div><div class="stat-label">Processed</div><div class="stat-value">{{ $import->processed_rows }}</div></div></div>
    <div class="card stat {{ $import->success_rows ? 'green' : '' }}"><i class="icon-circle-check"></i><div><div class="stat-label">Imported</div><div class="stat-value">{{ $import->success_rows }}</div></div></div>
    <div class="card stat {{ $import->failed_rows ? 'red' : '' }}"><i class="icon-circle-alert"></i><div><div class="stat-label">Failed</div><div class="stat-value">{{ $import->failed_rows }}</div></div></div>
</div>

<div class="card card-body" style="margin-top:1.25rem">
    <div class="flex"><span class="strong">{{ $percent }}%</span><span class="xs muted">{{ $import->processed_rows }} of {{ $import->total_rows }} rows</span></div>
    <div class="bar" style="margin-top:0.5rem"><span class="{{ $import->failed_rows ? 'amber' : 'green' }}" style="width:{{ $percent }}%"></span></div>
</div>

<h2 class="section-title">Failed rows</h2>
<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th class="nowrap">Row</th><th>external_reference</th><th>Subject</th><th>Problems</th></tr>
        </thead>
        <tbody>
        @forelse ($failedRows as $failed)
            @php
                $row = json_decode($failed->row_data, true) ?? [];
                $problems = json_decode($failed->errors, true) ?? [];
            @endphp
            <tr>
                <td class="mono">{{ $failed->row_number }}</td>
                <td class="mono">{{ $row['external_reference'] ?? '' ?: '–' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($row['subject'] ?? '', 60) ?: '–' }}</td>
                <td>
                    @foreach ($problems as $field => $messages)
                        @foreach ($messages as $message)
                            <div class="xs"><span class="mono faint">{{ $field }}:</span> {{ $message }}</div>
                        @endforeach
                    @endforeach
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="center muted">{{ $status->isFinished() ? 'No failed rows.' : 'No failed rows so far.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('tickets.partials.pager', ['paginator' => $failedRows])
@endsection

@unless ($status->isFinished())
    @push('scripts')
    <script>
        // No polling endpoint: while the job runs, simply reload this page every 5 seconds.
        setTimeout(function () { window.location.reload(); }, 5000);
    </script>
    @endpush
@endunless
