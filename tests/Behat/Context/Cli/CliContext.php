<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Context\Cli;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Synolia\SyliusSchedulerCommandPlugin\Command\SynoliaSchedulerRunCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;

class CliContext implements Context
{
    /** @var KernelInterface */
    protected $kernel;

    /** @var Application */
    protected $application;

    /** @var CommandTester */
    protected $tester;

    /** @var Command */
    protected $command;

    /** @var string */
    protected $filePath;

    /** @var RepositoryInterface */
    protected $scheduledCommandRepository;

    /** @var EntityManagerInterface */
    protected $scheduledCommandManager;

    /** @var SharedStorageInterface */
    protected $sharedStorage;

    /** @var ExecuteScheduleCommand */
    protected $executeScheduleCommand;

    public function __construct(
        KernelInterface $kernel,
        RepositoryInterface $scheduledCommandRepository,
        EntityManagerInterface $scheduledCommandManager,
        SharedStorageInterface $sharedStorage,
        ExecuteScheduleCommand $executeScheduleCommand
    ) {
        $this->kernel = $kernel;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->scheduledCommandManager = $scheduledCommandManager;
        $this->sharedStorage = $sharedStorage;
        $this->executeScheduleCommand = $executeScheduleCommand;
    }

    /**
     * @Given I have a working command-line interface
     */
    public function iHaveAWorkingCommandLineInterface(): void
    {
        $this->application = new Application($this->kernel);
    }

    /**
     * @Then I should see :messagePart in the output
     */
    public function iShouldSeeInTheMessage(string $messagePart): void
    {
        Assert::assertContains($messagePart, $this->tester->getDisplay());
    }

    /**
     * @When I run scheduled commands
     */
    public function iRunScheduledCommands(): void
    {
        $this->application->add(
            new SynoliaSchedulerRunCommand(
                null,
                $this->scheduledCommandManager,
                $this->executeScheduleCommand
            )
        );
        $this->command = $this->application->find('synolia:scheduler-run');
        $this->tester = new CommandTester($this->command);
        $this->tester->execute(['command' => 'synolia:scheduler-run']);
    }

    /**
     * @Given it is executed immediately
     */
    public function itIsExecutedImmediately(): void
    {
        /** @var ScheduledCommandInterface $command */
        $command = $this->sharedStorage->get('command');
        $command->setExecuteImmediately(true);
        $this->scheduledCommandManager->flush();
    }

    /**
     * @Given this scheduled command has :value in :attribute
     */
    public function thisScheduledCommandHasIn(string $value, string $attribute): void
    {
        /** @var ScheduledCommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');
        $getter = 'get' . \ucfirst($attribute);
        $attributeType = gettype($schedule->$getter());
        $setter = 'set' . \ucfirst($attribute);

        if ($attributeType === 'double') {
            $schedule->$setter((float) $value);
        }
        if ($attributeType === 'integer') {
            $schedule->$setter((int) $value);
        }
        if ($schedule->$getter() === null || $schedule->$getter() === '') {
            $schedule->$setter($value);
        }

        $this->scheduledCommandRepository->add($schedule);
    }

    /**
     * @Then the file of this command must contain :messagePart
     */
    public function theFileOfThisCommandMustContain(string $messagePart): void
    {
        /** @var ScheduledCommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');
        $logFile = $this->kernel->getLogDir() . \DIRECTORY_SEPARATOR . $schedule->getLogFile();

        Assert::assertContains($messagePart, \file_get_contents($logFile));
    }

    /**
     * @Given this file not exit yet
     */
    public function thisFileNotExitYet(): void
    {
        /** @var ScheduledCommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');
        $logFile = $this->kernel->getLogDir() . \DIRECTORY_SEPARATOR . $schedule->getLogFile();

        @\unlink($logFile);
    }
}
