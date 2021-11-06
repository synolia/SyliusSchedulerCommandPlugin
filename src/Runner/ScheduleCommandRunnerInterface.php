<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Runner;

use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

interface ScheduleCommandRunnerInterface
{
    public function runImmediately(string $scheduledCommandId): bool;

    public function runFromCron(ScheduledCommandInterface $scheduledCommand): int;
}
