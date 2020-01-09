<?php

declare(strict_types=1);

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Parser;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\KernelInterface;
use Synolia\SyliusSchedulerCommandPlugin\Parser\CommandParser;

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
