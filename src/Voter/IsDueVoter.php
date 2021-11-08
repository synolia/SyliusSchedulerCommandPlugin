<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Voter;

use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

final class IsDueVoter implements IsDueVoterInterface
{
    /** @var array<\Synolia\SyliusSchedulerCommandPlugin\Checker\IsDueCheckerInterface> */
    private $isDueCheckers;

    public function __construct(\Traversable $checkers)
    {
        $this->isDueCheckers = iterator_to_array($checkers);
    }

    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        foreach ($this->isDueCheckers as $checker) {
            try {
                return $checker->isDue($command, $dateTime);
            } catch (IsNotDueException $isNotDueException) {
                continue;
            }
        }

        return false;
    }
}
