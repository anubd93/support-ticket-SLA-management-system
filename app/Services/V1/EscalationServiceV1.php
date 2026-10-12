<?php

namespace App\Services\V1;

use App\Enums\V1\ActivityType;
use App\Events\V1\SlaApproaching;
use App\Events\V1\SlaBreached;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EscalationServiceV1
{
    public const LEVEL_APPROACHING = 1;

    public const LEVEL_BREACHED = 2;

    private const CHUNK = 100;

    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1
    ) {}

    public function run(): array
    {
        $breached = 0;
        $approaching = 0;

        $this->ticketRepositoryV1->chunkDueForBreach(self::CHUNK, function ($tickets) use (&$breached) {
            foreach ($tickets as $ticket) {
                $breached += $this->escalateBreached($ticket) ? 1 : 0;
            }
        });

        $this->ticketRepositoryV1->chunkDueForWarning(self::CHUNK, function ($tickets) use (&$approaching) {
            foreach ($tickets as $ticket) {
                $approaching += $this->escalateApproaching($ticket) ? 1 : 0;
            }
        });

        return ['breached' => $breached, 'approaching' => $approaching];
    }

    private function escalateBreached(object $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            $now = now();

            $won = $this->ticketRepositoryV1
                ->setId($ticket->id)
                ->setSlaBreachedAt($now)
                ->setEscalatedAt($now)
                ->setEscalationLevel(self::LEVEL_BREACHED)
                ->markBreached();

            if ($won === 0) {
                return false;
            }

            $this->logActivity($ticket->id, ActivityType::SlaBreached, [
                'level' => self::LEVEL_BREACHED,
                'due_at' => Carbon::parse($ticket->sla_due_at)->toDateTimeString(),
            ]);

            SlaBreached::dispatch($ticket->id);

            return true;
        });
    }

    private function escalateApproaching(object $ticket): bool
    {
        return DB::transaction(function () use ($ticket) {
            $now = now();

            $won = $this->ticketRepositoryV1
                ->setId($ticket->id)
                ->setSlaWarningSentAt($now)
                ->setEscalatedAt($now)
                ->setEscalationLevel(self::LEVEL_APPROACHING)
                ->markWarningSent();

            if ($won === 0) {
                return false;
            }

            $this->logActivity($ticket->id, ActivityType::SlaWarning, [
                'level' => self::LEVEL_APPROACHING,
                'due_at' => Carbon::parse($ticket->sla_due_at)->toDateTimeString(),
            ]);

            SlaApproaching::dispatch($ticket->id);

            return true;
        });
    }

    private function logActivity(int $ticketId, ActivityType $type, array $new): void
    {
        $this->ticketActivityRepositoryV1
            ->setTicketId($ticketId)
            ->setUserId(null)
            ->setType($type)
            ->setOldValues(null)
            ->setNewValues($new)
            ->store();
    }
}
