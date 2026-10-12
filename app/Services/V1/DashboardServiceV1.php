<?php

namespace App\Services\V1;

use App\Enums\V1\TicketPriority;
use App\Models\User;
use App\Repositories\V1\TicketRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;

class DashboardServiceV1
{
    public const RECENT_DAYS = 30;

    private const LIST_LIMIT = 5;

    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly UserRepositoryV1 $userRepositoryV1
    ) {}

    public function getDashboard(User $user): array
    {
        $isStaff = $user->hasPermission('ticket.view_all');
        $isAgent = $user->hasPermission('ticket.claim');
        $since = now()->subDays(self::RECENT_DAYS);
        
        $tickets = $this->ticketRepositoryV1
            ->setOnlyCreatedBy($isStaff ? null : $user->id)
            ->setOnlyAssignedTo(null)
            ->setFilters([]);

        $row = $tickets->getDashboardCounts($isAgent ? $user->id : null, $since);
        $counts = array_map('intval', (array) $row);

        return [
            'isStaff' => $isStaff,
            'isAgent' => $isAgent,
            'recentDays' => self::RECENT_DAYS,
            'counts' => $counts,
            'slaMetPercent' => $counts['finished_recent'] > 0
                ? (int) round($counts['met_recent'] / $counts['finished_recent'] * 100)
                : null,
            'byPriority' => $this->priorityRows($tickets->countActiveByPriority()),
            'agents' => $isStaff ? $this->userRepositoryV1->getAgentWorkload($since) : collect(),
            'attention' => $isStaff ? $tickets->getNeedsAttention(self::LIST_LIMIT) : collect(),
            'recent' => $isStaff ? collect() : $tickets->getRecentlyUpdated(self::LIST_LIMIT),
        ];
    }

    private function priorityRows(array $totals): array
    {
        $max = max(1, ...array_values($totals ?: [0]));

        return array_map(fn (TicketPriority $priority) => [
            'priority' => $priority,
            'total' => $totals[$priority->value] ?? 0,
            'percent' => (int) round(($totals[$priority->value] ?? 0) / $max * 100),
        ], array_reverse(TicketPriority::cases()));
    }
}
