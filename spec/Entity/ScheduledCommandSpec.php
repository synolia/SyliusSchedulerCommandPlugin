<?php

declare(strict_types=1);

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Entity;

use PhpSpec\ObjectBehavior;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;

class ScheduledCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ScheduledCommand::class);
    }
}
