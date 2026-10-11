@extends('layouts.app')

@section('title', 'New ticket')

@section('content')
<div class="page-head">
    <div>
        <h1>New ticket</h1>
        <p>Describe the problem. The deadline depends on the priority.</p>
    </div>
    <div class="actions">
        <a href="{{ route('tickets.index') }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
    </div>
</div>

<div class="grid g-main">
    <form id="ticket-form" class="card" method="POST" action="{{ route('tickets.store') }}" novalidate>
        @csrf
        <div class="card-body">
            @include('tickets.partials.form', ['ticket' => null])

            <div class="form-foot">
                <a href="{{ route('tickets.index') }}" class="btn">Cancel</a>
                <button type="submit" class="btn btn-primary">Create ticket</button>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-head"><h2>Resolution time</h2></div>
        <table class="table">
            <tbody>
            @foreach (array_reverse(\App\Enums\V1\TicketPriority::cases()) as $priority)
                <tr>
                    <td>@include('tickets.partials.priority', ['priority' => $priority->value])</td>
                    <td class="right strong">{{ isset($slaHours[$priority->value]) ? $slaHours[$priority->value] . ' h' : '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // POST /tickets -> 201 { redirect } | 422 { errors }
    AjaxForm.handle(document.getElementById('ticket-form'));
</script>
@endpush
