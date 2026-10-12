<?php

namespace App\Enums\V1;

enum TicketSource: string
{
    case Web = 'web';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Import => 'CSV Import',
        };
    }
}
