{{--
    Search + filter bar. A plain GET form to the current list, so filters live in the URL
    (bookmarkable, Back works, pagination keeps them).
    Expects: $filters (validated query string), $filterOptions (from TicketServiceV1::getFilterOptions).
--}}
@php
    $value = fn (string $key) => $filters[$key] ?? '';
    $hasFilters = collect($filters)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
    $moreCount = collect(['customer', 'from', 'to'])->filter(fn ($key) => $value($key) !== '')->count();
@endphp

<form method="GET" action="{{ url()->current() }}" class="mb-2" id="ticket-filters">
    @if ($errors->any())
        <div class="callout red">
            <i class="icon-circle-alert"></i>
            <div>{{ $errors->first() }} The filters were cleared.</div>
        </div>
    @endif

    <div class="toolbar">
        <div class="input-icon" style="flex:1 1 240px;max-width:320px">
            <i class="icon-search"></i>
            <input type="search" name="keyword" class="input" maxlength="100" placeholder="Ticket ID or keyword" value="{{ $value('keyword') }}">
        </div>

        <select name="status" class="select" style="width:170px" aria-label="Status">
            <option value="">Any status</option>
            @foreach ($filterOptions['statuses'] as $key => $label)
                <option value="{{ $key }}" @selected($value('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="priority" class="select" style="width:140px" aria-label="Priority">
            <option value="">Any priority</option>
            @foreach ($filterOptions['priorities'] as $key => $label)
                <option value="{{ $key }}" @selected($value('priority') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <select name="category_id" class="select" style="width:150px" aria-label="Category">
            <option value="">All categories</option>
            @foreach ($filterOptions['categories'] as $category)
                <option value="{{ $category->id }}" @selected((string) $value('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        @if ($filterOptions['agents'])
            <select name="agent_id" class="select" style="width:170px" aria-label="Agent">
                <option value="">Any agent</option>
                <option value="none" @selected($value('agent_id') === 'none')>Unassigned</option>
                @foreach ($filterOptions['agents'] as $agent)
                    <option value="{{ $agent->id }}" @selected((string) $value('agent_id') === (string) $agent->id)>{{ $agent->name }}</option>
                @endforeach
            </select>
        @endif

        <select name="sla" class="select" style="width:150px" aria-label="SLA status">
            <option value="">Any SLA</option>
            @foreach ($filterOptions['sla'] as $key => $label)
                <option value="{{ $key }}" @selected($value('sla') === $key)>{{ $label }}</option>
            @endforeach
        </select>

        <button type="button" class="btn" data-more-filters><i class="icon-sliders-horizontal"></i>More{{ $moreCount ? " ({$moreCount})" : '' }}</button>
        <button type="submit" class="btn btn-primary">Search</button>
        @if ($hasFilters)
            <a href="{{ url()->current() }}" class="btn btn-ghost">Clear</a>
        @endif
    </div>

    <div class="toolbar" data-more-panel style="{{ $moreCount ? '' : 'display:none' }}">
        @if ($filterOptions['showCustomer'])
            <input type="search" name="customer" class="input" style="width:240px" maxlength="100" placeholder="Customer name or email" value="{{ $value('customer') }}" aria-label="Customer">
        @endif
        <label class="flex xs muted">Created from <input type="date" name="from" class="input" style="width:160px" value="{{ $value('from') }}"></label>
        <label class="flex xs muted">to <input type="date" name="to" class="input" style="width:160px" value="{{ $value('to') }}"></label>
    </div>
</form>

<script>
    
    document.querySelectorAll('[data-more-filters]').forEach(function (button) {
        button.addEventListener('click', function () {
            const panel = button.closest('form').querySelector('[data-more-panel]');
            panel.style.display = panel.style.display === 'none' ? '' : 'none';
        });
    });

    document.querySelectorAll('#ticket-filters').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('input, select').forEach(function (field) {
                if (field.name && field.value === '') field.disabled = true;
            });
        });
    });
</script>
