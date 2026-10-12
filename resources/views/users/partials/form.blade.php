{{--
    Shared fields for create and edit.
    Expects: $user (row with category_ids | null), $roles (id, name, display_name), $categories (id, name).
--}}
@php
    $agentRoleId = optional($roles->firstWhere('name', \App\Enums\V1\UserRole::Agent->value))->id;
    $selectedRole = $user->role_id ?? optional($roles->firstWhere('name', \App\Enums\V1\UserRole::Customer->value))->id;
    $isSelf = $user && (int) $user->id === (int) auth()->id();
@endphp

<div class="callout red" data-form-alert style="display:none">
    <i class="icon-circle-alert"></i>
    <div data-form-alert-text></div>
</div>

<div class="grid g-2">
    <div class="field">
        <label class="label" for="name">Name <span class="req">*</span></label>
        <input id="name" name="name" class="input" maxlength="255" value="{{ $user->name ?? '' }}">
        <div class="error" data-error-for="name" style="display:none"></div>
    </div>

    <div class="field">
        <label class="label" for="email">Email <span class="req">*</span></label>
        <input id="email" name="email" type="email" class="input" maxlength="255" value="{{ $user->email ?? '' }}">
        <div class="error" data-error-for="email" style="display:none"></div>
    </div>
</div>

<div class="grid g-2">
    <div class="field">
        <label class="label" for="role_id">Role <span class="req">*</span></label>
        <select id="role_id" @unless ($isSelf) name="role_id" @endunless class="select" data-agent-role="{{ $agentRoleId }}" @disabled($isSelf)>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((int) $selectedRole === (int) $role->id)>{{ $role->display_name }}</option>
            @endforeach
        </select>
        @if ($isSelf)
            {{-- A disabled select is not submitted; the server also refuses a change to your own role. --}}
            <input type="hidden" name="role_id" value="{{ $selectedRole }}">
            <div class="hint">You cannot change your own role.</div>
        @endif
        <div class="error" data-error-for="role_id" style="display:none"></div>
    </div>

    <div class="field">
        <label class="label" for="password">Password @if (! $user)<span class="req">*</span>@endif</label>
        <input id="password" name="password" type="password" class="input" maxlength="255" autocomplete="new-password" placeholder="{{ $user ? 'Leave empty to keep the current password' : 'At least 8 characters' }}">
        <div class="error" data-error-for="password" style="display:none"></div>
    </div>
</div>

<div data-agent-only>
    <div class="field">
        <span class="label">Category skills</span>
        <div class="flex wrap">
            @foreach ($categories as $category)
                <label class="check">
                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, $user->category_ids ?? []))>
                    {{ $category->name }}
                </label>
            @endforeach
        </div>
        <div class="hint">Auto-assignment only gives an agent tickets in these categories.</div>
        <div class="error" data-error-for="category_ids" style="display:none"></div>
    </div>
</div>

<script>
    // Category skills only apply to support agents.
    (function () {
        const role = document.getElementById('role_id');
        const agentOnly = document.querySelector('[data-agent-only]');
        if (!role || !agentOnly) return;

        function toggle() {
            agentOnly.style.display = role.value === role.dataset.agentRole ? '' : 'none';
        }

        role.addEventListener('change', toggle);
        toggle();
    })();
</script>
