<?php

declare(strict_types=1);

namespace Tests\Synolia\SchedulerCommandPlugin\Behat\Context\Cli;

use Behat\Behat\Context\Context;
use Doctrine\Common\Persistence\ObjectManager;
use PHPUnit\Framework\Assert;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Synolia\SchedulerCommandPlugin\Command\SynoliaSchedulerRunCommand;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

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

    /** @var ObjectManager */
    protected $scheduledCommandManager;

    /** @var SharedStorageInterface */
    protected $sharedStorage;

    /** @var ContainerInterface */
    private $container;

    public function __construct(
        KernelInterface $kernel,
        RepositoryInterface $scheduledCommandRepository,
        ContainerInterface $container,
        ObjectManager $scheduledCommandManager,
        SharedStorageInterface $sharedStorage
    ) {
        $this->kernel = $kernel;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->container = $container;
        $this->scheduledCommandManager = $scheduledCommandManager;
        $this->sharedStorage = $sharedStorage;
    }

    /**
     * @Given I have a working command-line interface
     */
    public function iHaveAWorkingCommandLineInterface()
    {
        $this->application = new Application($this->kernel);
    }

    /**
     * @Then I should see :messagePart in the output
     */
    public function iShouldSeeInTheMessage($messagePart)
    {
        Assert::assertContains($messagePart, $this->tester->getDisplay());
    }

    /**
     * @When I run scheduled commands
     */
    public function iRunScheduledCommands()
    {
        $this->application->add(
            new SynoliaSchedulerRunCommand(null, $this->scheduledCommandManager, '')
        );
        $this->command = $this->application->find('synolia:scheduler-run');
        $this->tester = new CommandTester($this->command);
        $this->tester->execute(['command' => 'synolia:scheduler-run']);
    }

    /**
     * @Given it is executed immediately
     */
    public function itIsExecutedImmediately()
    {
        /** @var ScheduledCommand $command */
        $command = $this->sharedStorage->get('command');
        $command->setExecuteImmediately(true);
        $this->scheduledCommandManager->flush();
    }
}
