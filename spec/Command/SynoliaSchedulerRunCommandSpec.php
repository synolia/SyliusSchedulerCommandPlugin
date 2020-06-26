<?php

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;
use Synolia\SyliusSchedulerCommandPlugin\Command\SynoliaSchedulerRunCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;

class SynoliaSchedulerRunCommandSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $scheduledCommandManager,
        ExecuteScheduleCommand $executeScheduleCommand,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository
    ) {
        $this->beConstructedWith("command", $scheduledCommandManager, $executeScheduleCommand, $scheduledCommandRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SynoliaSchedulerRunCommand::class);
        $this->shouldBeAnInstanceOf(Command::class);
    }
}
