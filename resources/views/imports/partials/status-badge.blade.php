{{-- Expects: $status (string value from imports.status) --}}
@php
    $importStatus = \App\Enums\V1\ImportStatus::from($status);
    $badgeColor = match ($importStatus) {
        \App\Enums\V1\ImportStatus::Completed => 'green',
        \App\Enums\V1\ImportStatus::CompletedWithErrors => 'amber',
        \App\Enums\V1\ImportStatus::Failed => 'red',
        \App\Enums\V1\ImportStatus::Processing => 'blue',
        default => '',
    };
@endphp
<span class="badge {{ $badgeColor }}">{{ $importStatus->label() }}</span>
