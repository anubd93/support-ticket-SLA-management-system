<?php

namespace App\Console\Commands;

use App\Services\V1\EscalationServiceV1;
use Illuminate\Console\Command;

/**
 * Scheduled every minute in routes/console.php. Can also be run by hand to test.
 */
class CheckSlaCommand extends Command
{
    protected $signature = 'tickets:check-sla';

    protected $description = 'Escalate tickets that are approaching or have breached their SLA';

    public function handle(EscalationServiceV1 $escalationServiceV1): int
    {
        $result = $escalationServiceV1->run();

        $this->info("SLA check done: {$result['breached']} breached, {$result['approaching']} approaching.");

        return self::SUCCESS;
    }
}
