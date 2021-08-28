<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

interface IsDueCheckerInterface
{
    public const TAG_ID = 'synolia_scheduler.checker.is_due';

    public static function getDefaultPriority(): int;

    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool;
}
