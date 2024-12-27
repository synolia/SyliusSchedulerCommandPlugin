<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

interface IsDueCheckerInterface
{
    public static function getDefaultPriority(): int;

    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool;
}
