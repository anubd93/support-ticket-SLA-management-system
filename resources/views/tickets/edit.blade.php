@extends('layouts.app')

@section('title', 'Edit ' . $ticket->ticket_number)

@section('content')
<div class="page-head">
    <div>
        <span class="eyebrow mono">{{ $ticket->ticket_number }}</span>
        <h1>Edit ticket</h1>
        <p>Changing the priority recalculates the SLA deadline.</p>
    </div>
    <div class="actions">
        <a href="{{ route('tickets.show', $ticket->id) }}" class="btn"><i class="icon-arrow-left"></i>Back</a>
    </div>
</div>

@if ($ticket->status === \App\Enums\V1\TicketStatus::Closed->value)
    <div class="callout">
        <i class="icon-lock"></i>
        <div>This ticket is closed. Closed tickets cannot be edited.</div>
    </div>
@else
    <div class="grid g-main">
        <form id="ticket-form" class="card" method="POST" action="{{ route('tickets.update', $ticket->id) }}" novalidate>
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('tickets.partials.form', ['ticket' => $ticket])

                <div class="form-foot">
                    <a href="{{ route('tickets.show', $ticket->id) }}" class="btn">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
@endif
@endsection

@push('scripts')
<script>
    // PUT /tickets/{id} -> 200 { redirect } | 422 { errors } or { message }
    const ticketForm = document.getElementById('ticket-form');
    if (ticketForm) {
        AjaxForm.handle(ticketForm);
    }
</script>
@endpush
