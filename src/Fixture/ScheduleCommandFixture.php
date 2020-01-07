<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

class ScheduleCommandFixture extends AbstractFixture
{
    /** @var OptionsResolver */
    private $optionsResolver;

    /** @var ObjectManager */
    private $manager;

    /** @var FactoryInterface */
    private $scheduledCommand;

    public function __construct(FactoryInterface $scheduledCommandFactory, EntityManagerInterface $entityManager)
    {
        $this->optionsResolver =
            (new OptionsResolver())
                ->setRequired('custom')
                ->setAllowedTypes('custom', 'array');

        $this->scheduledCommand = $scheduledCommandFactory;
        $this->manager = $entityManager;
    }

    public function load(array $options): void
    {
        $options = $this->optionsResolver->resolve($options);

        foreach ($options['custom'] as $option) {
            /** @var ScheduledCommand $scheduleCommand */
            $scheduleCommand = $this->scheduledCommand->createNew();
            $scheduleCommand
                ->setName($option['name'])
                ->setCommand($option['command'])
                ->setPriority($option['priority'])
                ->setCronExpression($option['cronExpression'])
                ->setExecuteImmediately($option['executeImmediately'])
                ->setDisabled($option['disabled'])
            ;

            $this->manager->persist($scheduleCommand);
        }

        $this->manager->flush();

        return;
    }

    public function getName(): string
    {
        return 'schedule_command';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
            ->arrayNode('custom')->isRequired()->arrayPrototype()->children()
            ->scalarNode('name')->isRequired()->end()
            ->scalarNode('command')->isRequired()->end()
            ->scalarNode('cronExpression')->isRequired()->end()
            ->integerNode('priority')->isRequired()->end()
            ->booleanNode('executeImmediately')->isRequired()->end()
            ->booleanNode('disabled')->isRequired()->end()
            ->end()
        ;
    }
}
