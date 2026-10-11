<?php

namespace App\Enums\V1;

/**
 * SLA state of a ticket. Calculated from sla_due_at at read time, not stored.
 */
enum SlaStatus: string
{
    case Within = 'within';
    case Approaching = 'approaching';
    case Breached = 'breached';
    case Paused = 'paused';

    public function label(): string
    {
        return match ($this) {
            self::Within => 'Within SLA',
            self::Approaching => 'Approaching Breach',
            self::Breached => 'SLA Breached',
            self::Paused => 'Paused',
        };
    }
}
