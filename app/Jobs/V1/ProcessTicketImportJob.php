<?php

namespace App\Jobs\V1;

use App\Services\V1\ImportServiceV1;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessTicketImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    
    public int $timeout = 3600;

    public function __construct(public int $importId)
    {
        $this->onQueue('imports');
    }

    public function handle(ImportServiceV1 $importServiceV1): void
    {
        $importServiceV1->process($this->importId);
    }
    
    public function failed(?Throwable $exception): void
    {
        app(ImportServiceV1::class)->markFailed($this->importId, $exception?->getMessage() ?? 'The import stopped unexpectedly.');
    }
}
