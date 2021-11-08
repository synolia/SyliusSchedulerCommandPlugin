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
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Runner\ScheduleCommandRunnerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Voter\IsDueVoterInterface;

final class CliContext implements Context
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Application */
    private $application;

    /** @var CommandTester */
    private $tester;

    /** @var Command */
    private $command;

    /** @var string */
    private $filePath;

    /** @var RepositoryInterface */
    private $scheduledCommandRepository;

    /** @var EntityManagerInterface */
    private $scheduledCommandManager;

    /** @var SharedStorageInterface */
    private $sharedStorage;

    /** @var ScheduleCommandRunnerInterface */
    private $scheduleCommandRunner;

    /** @var CommandRepositoryInterface */
    private $commandRepository;

    /** @var ScheduledCommandPlannerInterface */
    private $scheduledCommandPlanner;

    /** @var IsDueVoterInterface */
    private $isDueVoter;

    public function __construct(
        KernelInterface $kernel,
        SharedStorageInterface $sharedStorage,
        EntityManagerInterface $scheduledCommandManager,
        ScheduleCommandRunnerInterface $scheduleCommandRunner,
        CommandRepositoryInterface $commandRepository,
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        ScheduledCommandPlannerInterface $scheduledCommandPlanner,
        IsDueVoterInterface $isDueVoter
    ) {
        $this->kernel = $kernel;
        $this->sharedStorage = $sharedStorage;
        $this->scheduledCommandManager = $scheduledCommandManager;
        $this->scheduleCommandRunner = $scheduleCommandRunner;
        $this->commandRepository = $commandRepository;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->scheduledCommandPlanner = $scheduledCommandPlanner;
        $this->isDueVoter = $isDueVoter;
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
                $this->scheduledCommandManager,
                $this->scheduleCommandRunner,
                $this->commandRepository,
                $this->scheduledCommandRepository,
                $this->scheduledCommandPlanner,
                $this->isDueVoter
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
        /** @var CommandInterface $command */
        $command = $this->sharedStorage->get('command');
        $command->setExecuteImmediately(true);
        $this->scheduledCommandManager->flush();
    }

    /**
     * @Given this command has :value in :attribute
     */
    public function thisCommandHasIn(string $value, string $attribute): void
    {
        /** @var CommandInterface $command */
        $command = $this->sharedStorage->get('command');
        $getter = 'get' . ucfirst($attribute);
        $attributeType = \gettype($command->$getter());
        $setter = 'set' . ucfirst($attribute);

        if ('double' === $attributeType) {
            $command->$setter((float) $value);
        }
        if ('integer' === $attributeType) {
            $command->$setter((int) $value);
        }
        if (null === $command->$getter() || '' === $command->$getter()) {
            $command->$setter($value);
        }

        $this->commandRepository->add($command);
    }

    /**
     * @Then the file of this command must contain :messagePart
     */
    public function theFileOfThisCommandMustContain(string $messagePart): void
    {
        /** @var ScheduledCommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');
        $logFile = $this->kernel->getLogDir() . \DIRECTORY_SEPARATOR . $schedule->getLogFile();

        Assert::assertContains($messagePart, file_get_contents($logFile));
    }

    /**
     * @Given this file not exit yet
     */
    public function thisFileNotExitYet(): void
    {
        /** @var CommandInterface $schedule */
        $schedule = $this->sharedStorage->get('command');
        $logFile = $this->kernel->getLogDir() . \DIRECTORY_SEPARATOR . $schedule->getLogFilePrefix();

        @unlink($logFile);
    }
}
