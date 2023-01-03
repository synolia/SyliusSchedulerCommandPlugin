<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Planner;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

class ScheduledCommandPlanner implements ScheduledCommandPlannerInterface
{
    public function __construct(
        private FactoryInterface $scheduledCommandFactory,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function plan(CommandInterface $command): ScheduledCommandInterface
    {
        /** @var ScheduledCommandInterface $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandFactory->createNew();

        $scheduledCommand
            ->setName($command->getName())
            ->setCommand($command->getCommand())
            ->setArguments($command->getArguments())
            ->setTimeout($command->getTimeout())
            ->setIdleTimeout($command->getIdleTimeout())
            ->setOwner($command)
        ;

        if (null !== $command->getLogFilePrefix() && '' !== $command->getLogFilePrefix()) {
            $scheduledCommand->setLogFile(\sprintf(
                '%s-%s-%s.log',
                $command->getLogFilePrefix(),
                (new \DateTime())->format('Y-m-d'),
                \uniqid(),
            ));
        }

        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();

        $this->logger->info('Command has been planned for execution.', ['command_name' => $command->getName()]);

        return $scheduledCommand;
    }
}
