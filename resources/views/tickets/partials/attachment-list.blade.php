{{-- Expects: $files (rows or Attachment models with id, original_name, mime_type, size) --}}
@if (count($files ?? []))
    <div class="attachments">
        @foreach ($files as $file)
            @php
                $icon = match (true) {
                    str_starts_with($file->mime_type, 'image/') => 'icon-image',
                    $file->mime_type === 'application/pdf' => 'icon-file-text',
                    default => 'icon-paperclip',
                };
            @endphp
            <a href="{{ route('attachments.download', $file->id) }}" class="attachment" title="Download {{ $file->original_name }}">
                <i class="{{ $icon }}"></i>{{ $file->original_name }} <small>{{ \Illuminate\Support\Number::fileSize($file->size) }}</small>
            </a>
        @endforeach
    </div>
@endif
