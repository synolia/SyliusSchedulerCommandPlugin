<?php

declare(strict_types=1);

namespace spec\Synolia\SchedulerCommandPlugin\Parser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\KernelInterface;
use Synolia\SchedulerCommandPlugin\Parser\CommandParser;

class CommandParserSpec extends ObjectBehavior
{
    function let(KernelInterface $kernel)
    {
        $this->beConstructedWith($kernel, []);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CommandParser::class);
    }
}
