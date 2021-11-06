<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Service;

use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

interface ExecuteScheduleCommandInterface
{
    public function executeImmediate(string $scheduledCommandId): bool;

    public function executeFromCron(ScheduledCommandInterface $scheduledCommand): int;
}
