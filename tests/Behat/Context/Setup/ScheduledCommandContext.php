<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class ScheduledCommandContext implements Context
{
    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var FactoryInterface */
    private $scheduledCommandFactory;

    /** @var ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        FactoryInterface $scheduledCommandFactory,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->scheduledCommandFactory = $scheduledCommandFactory;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
    }

    /**
     * @Given I have scheduled command :code named :name
     */
    public function iHaveCommandNamed(string $code, string $name): void
    {
        /** @var ScheduledCommandInterface $command */
        $command = $this->scheduledCommandFactory->createNew();
        $command->setCommand($code)
            ->setName($name);

        $this->sharedStorage->set('scheduled_command', $command);
        $this->scheduledCommandRepository->add($command);
    }

    /**
     * @Given this scheduled command has :value in :attribute
     */
    public function thisScheduledCommandHasIn(string $value, string $attribute): void
    {
        /** @var ScheduledCommandInterface $scheduledCommand */
        $scheduledCommand = $this->sharedStorage->get('scheduled_command');
        $getter = 'get' . \ucfirst($attribute);
        $attributeType = gettype($scheduledCommand->$getter());
        $setter = 'set' . \ucfirst($attribute);

        if ($attributeType === 'double') {
            $scheduledCommand->$setter((float) $value);
        }
        if ($attributeType === 'integer') {
            $scheduledCommand->$setter((int) $value);
        }
        if ($scheduledCommand->$getter() === null || $scheduledCommand->$getter() === '') {
            $scheduledCommand->$setter($value);
        }

        $this->scheduledCommandRepository->add($scheduledCommand);
    }
}
