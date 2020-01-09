<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

final class CommandContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var FactoryInterface */
    private $scheduledCommandFactory;

    /** @var RepositoryInterface */
    private $scheduledCommandRepository;

    /** @var ObjectManager */
    private $scheduledCommandManager;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        FactoryInterface $scheduledCommandFactory,
        RepositoryInterface $scheduledCommandRepository,
        ObjectManager $scheduledCommandManager
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->scheduledCommandFactory = $scheduledCommandFactory;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->scheduledCommandManager = $scheduledCommandManager;
    }

    /**
     * @Given I have command :code named :name
     */
    public function iHaveCommandNamed(string $code, string $name): void
    {
        /** @var ScheduledCommandInterface $command */
        $command = $this->scheduledCommandFactory->createNew();
        $command->setCommand($code)
            ->setName($name);

        $this->sharedStorage->set('command', $command);
        $this->scheduledCommandRepository->add($command);
    }
}
