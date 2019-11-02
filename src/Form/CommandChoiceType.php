<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SchedulerCommandPlugin\Parser\CommandParserInterface;

class CommandChoiceType extends AbstractType
{
    /**
     * @var CommandParserInterface
     */
    private $commandParser;

    public function __construct(CommandParserInterface $commandParser)
    {
        $this->commandParser = $commandParser;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'choices' => $this->commandParser->getCommands(),
            ]
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
