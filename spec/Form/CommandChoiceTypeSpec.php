<?php

declare(strict_types=1);

namespace spec\Synolia\SyliusSchedulerCommandPlugin\Form;

use PhpSpec\ObjectBehavior;
use Synolia\SyliusSchedulerCommandPlugin\Form\CommandChoiceType;
use Synolia\SyliusSchedulerCommandPlugin\Parser\CommandParserInterface;

final class CommandChoiceTypeSpec extends ObjectBehavior
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
