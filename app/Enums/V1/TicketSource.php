<?php

namespace App\Enums\V1;

enum TicketSource: string
{
    case Web = 'web';
    case Api = 'api';
    case Import = 'import';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::Api => 'API',
            self::Import => 'CSV Import',
        };
    }
}
