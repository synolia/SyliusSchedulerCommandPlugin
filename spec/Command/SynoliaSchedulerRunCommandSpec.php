<?php

namespace spec\Synolia\SchedulerCommandPlugin\Command;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Console\Command\Command;
use Synolia\SchedulerCommandPlugin\Command\SynoliaSchedulerRunCommand;

class SynoliaSchedulerRunCommandSpec extends ObjectBehavior
{
    function let(
        EntityManagerInterface $scheduledCommandManager
    ) {
        $this->beConstructedWith("", $scheduledCommandManager, "");
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(SynoliaSchedulerRunCommand::class);
        $this->shouldBeAnInstanceOf(Command::class);
    }
}
