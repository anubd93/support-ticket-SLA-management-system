<?php

namespace App\Services\V1;

use App\Repositories\V1\TicketSequenceRepositoryV1;

class TicketNumberServiceV1
{
    public function __construct(private readonly TicketSequenceRepositoryV1 $ticketSequenceRepositoryV1
    ) {}

    /**
     * Next number like TKT-2026-000001. Call inside a DB transaction.
     */
    public function generate(): string
    {
        $year = (int) now()->format('Y');
        $number = $this->ticketSequenceRepositoryV1->setYear($year)->nextNumber();

        return sprintf(
            '%s-%d-%s',
            config('tickets.number_prefix'),
            $year,
            str_pad((string) $number, config('tickets.number_padding'), '0', STR_PAD_LEFT)
        );
    }
}
