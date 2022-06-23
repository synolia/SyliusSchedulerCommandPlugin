<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Command;

use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Synolia\SyliusSchedulerCommandPlugin\Command\PurgeScheduledCommandCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

final class PurgeScheduledCommandCommandTest extends KernelTestCase
{
    use WithDatabaseTrait;

    private EntityManagerInterface $entityManager;

    private ReflectionClass $reflectionClass;

    public function setUp(): void
    {
        $kernel = static::bootKernel();
        self::initDatabase($kernel);

        $this->entityManager = static::$container->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        $this->reflectionClass = new ReflectionClass(PurgeScheduledCommandCommand::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testExecuteWithoutArgument(): void
    {
        $commandTester = $this->createCommandTester();
        $commandTester->execute([]);

        $days = $this->reflectionClass->getConstant('DEFAULT_PURGE_PERIODE_IN_DAYS');
        $now = new \DateTime();
        $now->modify(\sprintf('-%d days', $days));

        self::assertStringContainsString(
            \sprintf(
                'Schedules with states ["finished"] lesser than %d days(s) (%s)',
                $days,
                $now->format('Y-m-d'),
            ),
            $commandTester->getDisplay(true)
        );
    }

    public function testExecuteWithDaysArgument(): void
    {
        $commandTester = $this->createCommandTester();
        $commandTester->execute(['--days' => 0], ['interactive' => false]);

        $days = 0;
        $now = new \DateTime();
        $now->modify(\sprintf('-%d days', $days));

        self::assertStringContainsString(
            \sprintf(
                'Schedules with states ["finished"] lesser than %d days(s) (%s)',
                $days,
                $now->format('Y-m-d'),
            ),
            $commandTester->getDisplay(true)
        );
    }

    /** @dataProvider scheduleStateDataProvider */
    public function testExecuteWithStateArgument(array $states, string $expected): void
    {
        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--state' => $states,
        ], [
            'interactive' => false,
        ]);

        self::assertStringContainsString(
            \sprintf(
                'Schedules with states ["%s"]',
                $expected
            ),
            $commandTester->getDisplay(true)
        );
    }

    public function scheduleStateDataProvider(): \Generator
    {
        yield [[ScheduledCommandStateEnum::FINISHED], ScheduledCommandStateEnum::FINISHED];
        yield [[ScheduledCommandStateEnum::WAITING], ScheduledCommandStateEnum::WAITING];
        yield [
            [ScheduledCommandStateEnum::FINISHED, ScheduledCommandStateEnum::WAITING],
            ScheduledCommandStateEnum::FINISHED . ',' . ScheduledCommandStateEnum::WAITING,
        ];
    }

    public function testPurgeAllWithDryRun(): void
    {
        $scheduledCommand = $this->generateScheduleCommand(
            'about',
            'about',
            ScheduledCommandStateEnum::FINISHED
        );

        $this->save($scheduledCommand);

        $commandTester = $this->createCommandTester();
        $commandTester->execute([
            '--all' => true,
            '--dry-run' => true,
        ], [
            'interactive' => false,
            'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ]);

        $count = $this->entityManager->getRepository(ScheduledCommandInterface::class)->count([]);
        $this->assertEquals(1, $count);
    }

    public function testPurgeAllWithoutDryRun(): void
    {
        $scheduledCommand = $this->generateScheduleCommand(
            'about',
            'about',
            ScheduledCommandStateEnum::FINISHED
        );
        $this->save($scheduledCommand);

        $scheduledCommand = $this->generateScheduleCommand(
            'about1',
            'about1',
            ScheduledCommandStateEnum::FINISHED
        );
        $this->save($scheduledCommand);

        $this->createCommandTester()->execute([
            '--all' => true,
        ], [
            'interactive' => false,
            'verbosity' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ]);

        $count = $this->entityManager->getRepository(ScheduledCommandInterface::class)->count([]);
        $this->assertEquals(0, $count);
    }

    private function generateScheduleCommand(string $name, string $command, string $state): ScheduledCommandInterface
    {
        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = (new Factory(ScheduledCommand::class))->createNew();
        $scheduledCommand
            ->setName($name)
            ->setCommand($command)
            ->setState($state)
        ;

        return $scheduledCommand;
    }

    private function save(ScheduledCommandInterface $scheduledCommand): void
    {
        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();
    }

    private function createCommandTester(): CommandTester
    {
        $application = new Application(static::$kernel);
        $command = $application->find(PurgeScheduledCommandCommand::getDefaultName());

        return new CommandTester($command);
    }
}
