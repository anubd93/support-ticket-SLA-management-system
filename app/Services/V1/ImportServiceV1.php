<?php

namespace App\Services\V1;

use App\Repositories\V1\ImportErrorRepositoryV1;
use App\Repositories\V1\ImportRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;

class ImportServiceV1
{
    public function __construct(private readonly ImportRepositoryV1 $importRepositoryV1,
                                private readonly ImportErrorRepositoryV1 $importErrorRepositoryV1,
                                private readonly TicketRepositoryV1 $ticketRepositoryV1
    ) {}


}
