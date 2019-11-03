<?php

namespace spec\Synolia\SchedulerCommandPlugin\Entity;

use PhpSpec\ObjectBehavior;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

class ScheduledCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ScheduledCommand::class);
    }
}
