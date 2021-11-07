<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Planner;

use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

interface ScheduledCommandPlannerInterface
{
    public function plan(CommandInterface $command): ScheduledCommandInterface;
}
