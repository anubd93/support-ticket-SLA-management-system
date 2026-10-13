@extends('layouts.app')

@section('title', 'All imports')

@section('content')
<div class="page-head">
    <div>
        <h1>All imports</h1>
        <p>Every CSV import, newest first. Open one to see its failed rows.</p>
    </div>
    <div class="actions">
        <a href="{{ route('imports.create') }}" class="btn btn-primary"><i class="icon-plus"></i>New import</a>
    </div>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
        <tr><th>File</th><th>Status</th><th>Uploaded by</th><th class="right">Rows</th><th class="right">Imported</th><th class="right">Failed</th><th class="right">Uploaded</th><th class="w-actions"></th></tr>
        </thead>
        <tbody>
        @forelse ($imports as $import)
            <tr>
                <td><a href="{{ route('imports.show', $import->id) }}" class="ticket-link">{{ $import->original_name }}</a></td>
                <td>@include('imports.partials.status-badge', ['status' => $import->status])</td>
                <td>{{ $import->uploaded_by }}</td>
                <td class="right">{{ $import->processed_rows }} / {{ $import->total_rows }}</td>
                <td class="right">{{ $import->success_rows }}</td>
                <td class="right {{ $import->failed_rows ? 'text-red' : '' }}">{{ $import->failed_rows }}</td>
                <td class="right muted nowrap" title="{{ \Illuminate\Support\Carbon::parse($import->created_at)->format('d M Y, H:i') }}">{{ \Illuminate\Support\Carbon::parse($import->created_at)->diffForHumans() }}</td>
                <td class="w-actions"><a href="{{ route('imports.show', $import->id) }}" class="icon-btn" aria-label="View"><i class="icon-eye"></i></a></td>
            </tr>
        @empty
            <tr><td colspan="8" class="center muted">No imports yet. <a href="{{ route('imports.create') }}">Start one</a>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@include('tickets.partials.pager', ['paginator' => $imports])
@endsection
