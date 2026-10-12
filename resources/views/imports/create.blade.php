@extends('layouts.app')

@section('title', 'CSV import')

@section('content')
<div class="page-head">
    <div>
        <h1>CSV import</h1>
        <p>Import tickets from another system. Large files are processed in the background.</p>
    </div>
    <div class="actions">
        <a href="{{ route('imports.index') }}" class="btn"><i class="icon-list"></i>All imports</a>
        <a href="{{ route('imports.template') }}" class="btn"><i class="icon-download"></i>Download template</a>
    </div>
</div>

<div class="grid g-main">
    <form id="import-form" class="card" method="POST" action="{{ route('imports.store') }}" enctype="multipart/form-data" novalidate>
        @csrf
        <div class="card-head"><h2>Upload a CSV file</h2></div>
        <div class="card-body">
            <div class="callout red" data-form-alert style="display:none">
                <i class="icon-circle-alert"></i>
                <div data-form-alert-text></div>
            </div>
            <div class="field">
                <label class="label" for="file">CSV file <span class="req">*</span></label>
                <input id="file" name="file" type="file" class="input" accept=".csv,text/csv">
                <div class="hint">Up to {{ round(config('tickets.import.max_kb') / 1024) }} MB. The first row must be the column names.</div>
                <div class="error" data-error-for="file" style="display:none"></div>
            </div>
            <div class="form-foot">
                <button type="submit" class="btn btn-primary">Start import</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-head"><h2>Columns</h2></div>
        <table class="table">
            <tbody>
            <tr><td class="mono">external_reference</td><td class="xs muted">required, unique ID from the old system</td></tr>
            <tr><td class="mono">subject</td><td class="xs muted">required, max 255</td></tr>
            <tr><td class="mono">description</td><td class="xs muted">required</td></tr>
            <tr><td class="mono">priority</td><td class="xs muted">low, medium, high, critical</td></tr>
            <tr><td class="mono">category</td><td class="xs muted">category name, e.g. Billing</td></tr>
            <tr><td class="mono">customer_email</td><td class="xs muted">an active customer</td></tr>
            <tr><td class="mono">agent_email</td><td class="xs muted">optional; empty = auto-assign</td></tr>
            </tbody>
        </table>
        <div class="card-body xs muted">Rows whose external_reference was already imported are skipped, so a file can safely be uploaded again.</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    AjaxForm.handle(document.getElementById('import-form'));
</script>
@endpush
