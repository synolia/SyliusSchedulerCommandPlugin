<?php

declare(strict_types=1);

namespace spec\Synolia\SchedulerCommandPlugin\Form;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Synolia\SchedulerCommandPlugin\Form\CommandChoiceType;
use Synolia\SchedulerCommandPlugin\Parser\CommandParserInterface;

class CommandChoiceTypeSpec extends ObjectBehavior
{
    function let(CommandParserInterface $commandParser)
    {
        $this->beConstructedWith($commandParser);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CommandChoiceType::class);
    }
}
