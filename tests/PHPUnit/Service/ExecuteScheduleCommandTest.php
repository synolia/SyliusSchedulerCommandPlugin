<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Runner\ScheduleCommandRunnerInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

class ExecuteScheduleCommandTest extends WebTestCase
{
    use WithDatabaseTrait;

    /** @var ScheduleCommandRunnerInterface */
    private $scheduleCommandRunner;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        self::initDatabase($kernel);

        $this->scheduleCommandRunner = self::$container->get(ScheduleCommandRunnerInterface::class);
        $this->entityManager = self::$container->get(EntityManagerInterface::class);
    }

    public function testExecuteImmediateWithWrongCommand(): void
    {
        $commandResult = $this->scheduleCommandRunner->runImmediately('hello world !');

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

        /** @var ScheduledCommandPlannerInterface $planner */
        $planner = self::$container->get(ScheduledCommandPlannerInterface::class);
        $scheduledCommand = $planner->plan($command);

        $commandResult = $this->scheduleCommandRunner->runImmediately((string) $scheduledCommand->getId());
        $this->assertEquals(true, $commandResult);
    }
}
