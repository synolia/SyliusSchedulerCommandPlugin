<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Fixture;

use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class SchedulerCommandFixture extends AbstractFixture
{
    /** @var RepositoryInterface */
    private $commandRepository;

    /** @var \Sylius\Component\Resource\Factory\FactoryInterface */
    private $commandFactory;

    public function __construct(
        RepositoryInterface $commandRepository,
        FactoryInterface $commandFactory
    ) {
        $this->commandRepository = $commandRepository;
        $this->commandFactory = $commandFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $options): void
    {
        if (!\is_array($options['scheduled_commands'])) {
            return;
        }

        foreach ($options['scheduled_commands'] as $scheduledCommandArray) {
            /** @var \Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface $scheduledCommand */
            $scheduledCommand = $this->commandFactory->createNew();
            $scheduledCommand
                ->setName($scheduledCommandArray['name'])
                ->setCommand($scheduledCommandArray['command'])
                ->setArguments($scheduledCommandArray['arguments'])
                ->setCronExpression($scheduledCommandArray['cronExpression'])
                ->setLogFilePrefix($scheduledCommandArray['logFilePrefix'])
                ->setPriority($scheduledCommandArray['priority'])
                ->setExecuteImmediately($scheduledCommandArray['executeImmediately'])
                ->setEnabled($scheduledCommandArray['enabled'])
            ;
            $this->commandRepository->add($scheduledCommand);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'scheduler_command';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
            ->arrayNode('scheduled_commands')->arrayPrototype()->children()
            ->scalarNode('name')->isRequired()->end()
            ->scalarNode('command')->isRequired()->end()
            ->scalarNode('arguments')->defaultValue('')->end()
            ->scalarNode('cronExpression')->isRequired()->end()
            ->scalarNode('logFilePrefix')->defaultValue('')->end()
            ->integerNode('priority')->isRequired()->end()
            ->booleanNode('executeImmediately')->defaultFalse()->end()
            ->booleanNode('enabled')->defaultTrue()->end()
            ->end()
            ->end();
    }
}
