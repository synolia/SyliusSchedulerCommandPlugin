<?php

declare(strict_types=1);

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;
use Synolia\SyliusSchedulerCommandPlugin\Command\SynoliaSchedulerRunCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner;
use Synolia\SyliusSchedulerCommandPlugin\Voter\IsDueVoterInterface;

class SynoliaSchedulerRunCommandSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $scheduledCommandManager,
        ExecuteScheduleCommandInterface $executeScheduleCommand,
        CommandRepositoryInterface $commandRepository,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        ScheduledCommandPlanner $scheduledCommandPlanner,
        IsDueVoterInterface $isDueVoter
    ) {
        $this->beConstructedWith(
            $scheduledCommandManager,
            $executeScheduleCommand,
            $commandRepository,
            $scheduledCommandRepository,
            $scheduledCommandPlanner,
            $isDueVoter
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SynoliaSchedulerRunCommand::class);
        $this->shouldBeAnInstanceOf(Command::class);
    }
}
