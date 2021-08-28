<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Voter;

use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

interface IsDueVoterInterface
{
    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool;
}
