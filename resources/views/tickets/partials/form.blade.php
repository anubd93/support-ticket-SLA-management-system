{{--
    Shared fields for create and edit.
    Expects: $ticket (row|null), $categories (id, name), $slaHours (priority => hours).
--}}
<div class="callout red" data-form-alert style="display:none">
    <i class="icon-circle-alert"></i>
    <div data-form-alert-text></div>
</div>

<div class="field">
    <label class="label" for="subject">Subject <span class="req">*</span></label>
    <input id="subject" name="subject" class="input" maxlength="255" placeholder="Short summary of the issue" value="{{ $ticket->subject ?? '' }}">
    <div class="error" data-error-for="subject" style="display:none"></div>
</div>

<div class="grid g-2">
    <div class="field">
        <label class="label" for="category_id">Category <span class="req">*</span></label>
        <select id="category_id" name="category_id" class="select">
            <option value="">Choose a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(($ticket->category_id ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <div class="error" data-error-for="category_id" style="display:none"></div>
    </div>

    <div class="field">
        <label class="label" for="priority">Priority <span class="req">*</span></label>
        <select id="priority" name="priority" class="select">
            @foreach (\App\Enums\V1\TicketPriority::cases() as $priority)
                <option value="{{ $priority->value }}" @selected(($ticket->priority ?? 'medium') === $priority->value)>
                    {{ $priority->label() }}@isset($slaHours[$priority->value]) · {{ $slaHours[$priority->value] }} h @endisset
                </option>
            @endforeach
        </select>
        <div class="error" data-error-for="priority" style="display:none"></div>
    </div>
</div>

<div class="field">
    <label class="label" for="description">Description <span class="req">*</span></label>
    <textarea id="description" name="description" class="textarea" rows="8" maxlength="10000" placeholder="What happened, what you expected, and steps to reproduce">{{ $ticket->description ?? '' }}</textarea>
    <div class="error" data-error-for="description" style="display:none"></div>
</div>
