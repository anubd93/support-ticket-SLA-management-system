<?php

namespace App\Http\Requests\V1\Concerns;

trait AttachmentRules
{
    protected function attachmentRules(): array
    {
        return [
            'attachments' => ['nullable', 'array', 'max:' . config('tickets.attachments.max_files')],
            'attachments.*' => [
                'file',
                'max:' . config('tickets.attachments.max_kb'),
                'mimes:' . implode(',', config('tickets.attachments.mimes')),
            ],
        ];
    }

    protected function attachmentMessages(): array
    {
        $maxMb = round(config('tickets.attachments.max_kb') / 1024, 1);

        return [
            'attachments.max' => 'You can attach up to ' . config('tickets.attachments.max_files') . ' files.',
            'attachments.*.file' => 'Each attachment must be a file.',
            'attachments.*.uploaded' => 'A file failed to upload. It may be larger than the server allows.',
            'attachments.*.max' => "Each file must be {$maxMb} MB or smaller.",
            'attachments.*.mimes' => 'Allowed file types: ' . implode(', ', config('tickets.attachments.mimes')) . '.',
        ];
    }
}
