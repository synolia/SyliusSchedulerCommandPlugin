<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Planner;

use function Amp\Promise\first;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

class ScheduledCommandPlannerTest extends KernelTestCase
{
    use WithDatabaseTrait;

    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        self::initDatabase($kernel);
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function testPlanACommand(): void
    {
        $command = $this->getCommand();

        /** @var ScheduledCommandPlannerInterface $planner */
        $planner = static::getContainer()->get(ScheduledCommandPlannerInterface::class);
        $scheduledCommand = $planner->plan($command);

        $count = $this->entityManager->getRepository(ScheduledCommandInterface::class)->count([]);
        $this->assertEquals(1, $count);

        $scheduledCommandsOnDatabase = $this->entityManager->getRepository(ScheduledCommandInterface::class)->findBy([
            'command' => 'about',
            'state' => ScheduledCommandStateEnum::WAITING,
        ]);
        $this->assertCount(1, $scheduledCommandsOnDatabase);
        /** @var ScheduledCommandInterface $scheduledCommandOnDatabase */
        $scheduledCommandOnDatabase = $scheduledCommandsOnDatabase[0];
        $this->assertSame($scheduledCommand->getCommand(), $scheduledCommandOnDatabase->getCommand());
    }

    public function testPlanACommandTwice(): void
    {
        $command = $this->getCommand();

        /** @var ScheduledCommandPlannerInterface $planner */
        $planner = static::getContainer()->get(ScheduledCommandPlannerInterface::class);
        // first plan
        $planner->plan($command);
        // seconde plan
        $planner->plan($command);

        $scheduledCommandsOnDatabase = $this->entityManager->getRepository(ScheduledCommandInterface::class)->findBy([
            'command' => 'about',
            'state' => ScheduledCommandStateEnum::WAITING,
        ]);

        // Command must by scheduled only once time
        $this->assertCount(1, $scheduledCommandsOnDatabase);
    }

    private function getCommand(): Command
    {
        /** @var Command $command */
        $command = (new Factory(Command::class))->createNew();
        $command
            ->setName('Plan Command')
            ->setCommand('about')
        ;

        $this->entityManager->persist($command);
        $this->entityManager->flush();

        return $command;
    }
}
