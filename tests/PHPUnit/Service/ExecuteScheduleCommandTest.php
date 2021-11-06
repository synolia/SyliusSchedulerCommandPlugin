<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

class ExecuteScheduleCommandTest extends WebTestCase
{
    use WithDatabaseTrait;

    /** @var ExecuteScheduleCommandInterface */
    private $executeScheduleCommand;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        self::initDatabase($kernel);

        $this->executeScheduleCommand = self::$container->get(ExecuteScheduleCommandInterface::class);
        $this->entityManager = self::$container->get(EntityManagerInterface::class);
    }

    public function testExecuteImmediateWithWrongCommand(): void
    {
        $commandResult = $this->executeScheduleCommand->executeImmediate('hello world !');

        $this->assertEquals(false, $commandResult);
    }

    public function testExecuteImmediateWithGoodCommand(): void
    {
        /** @var Command $command */
        $command = (new Factory(Command::class))->createNew();
        $command
            ->setName('About application')
            ->setCommand('about')
        ;

        $this->entityManager->persist($command);
        $this->entityManager->flush();

        /** @var ScheduledCommandPlanner $planner */
        $planner = self::$container->get(ScheduledCommandPlanner::class);
        $scheduledCommand = $planner->plan($command);

        $commandResult = $this->executeScheduleCommand->executeImmediate((string) $scheduledCommand->getId());
        $this->assertEquals(true, $commandResult);
    }
}
