<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;

final class CommandContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var FactoryInterface */
    private $commandFactory;

    /** @var RepositoryInterface */
    private $commandRepository;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        FactoryInterface $commandFactory,
        CommandRepositoryInterface $commandRepository,
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->commandFactory = $commandFactory;
        $this->commandRepository = $commandRepository;
    }

    /**
     * @Given I have command :code named :name
     */
    public function iHaveCommandNamed(string $code, string $name): void
    {
        /** @var ScheduledCommandInterface $command */
        $command = $this->commandFactory->createNew();
        $command->setCommand($code)
            ->setName($name)
        ;

        $this->sharedStorage->set('command', $command);
        $this->commandRepository->add($command);
    }
}
