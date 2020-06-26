<?php

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

final class SynoliaSchedulerRunCommandTest extends KernelTestCase
{
    use WithDatabaseTrait;

    private static $commandName = 'synolia:scheduler-run';

    public function setUp(): void
    {
        $kernel = static::bootKernel();
        self::initDatabase($kernel);
    }

    public function testExecuteWithoutCommandInDatabase(): void
    {
        $application = new Application(static::$kernel);

        $command = $application->find(self::$commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertStringContainsString('Nothing to do', $commandTester->getDisplay());
    }

    public function testExecuteWithANewCommandThatShouldNotBeRunNow(): void
    {
        $scheduledCommand = $this->generateAboutScheduleCommand('0 0 31 2 0'); // 31 February, should never happen
        $this->save($scheduledCommand);

        $application = new Application(static::$kernel);

        $command = $application->find(self::$commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        self::assertStringContainsString('Nothing to do', $commandTester->getDisplay());
    }

    public function testExecuteWithANewCommandThatCronIsDue(): void
    {
        $scheduledCommand = $this->generateAboutScheduleCommand('* * * * *');
        $this->save($scheduledCommand);

        $application = new Application(static::$kernel);

        $command = $application->find(self::$commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        self::assertStringContainsString('Execute Command "about" - last execution : never', $commandTester->getDisplay());
        self::assertStringNotContainsString('Nothing to do', $commandTester->getDisplay());
    }

    public function testExecuteWithANewCommandThatItMarkedAsExecuteNow(): void
    {
        $scheduledCommand = $this->generateAboutScheduleCommand('0 0 31 2 0'); // 31 February, should never happen
        // but mark as execute now
        $scheduledCommand->setExecuteImmediately(true);
        $this->save($scheduledCommand);

        $application = new Application(static::$kernel);

        $command = $application->find(self::$commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        self::assertStringContainsString('Immediately execution asked for : about', $commandTester->getDisplay());
        self::assertStringContainsString('Execute Command "about" - last execution : never', $commandTester->getDisplay());
        self::assertStringNotContainsString('Nothing to do', $commandTester->getDisplay());
    }

    private function generateAboutScheduleCommand(string $cron): ScheduledCommand
    {
        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = (new Factory(ScheduledCommand::class))->createNew();
        $scheduledCommand
            ->setCronExpression($cron)
            ->setCommand('about');

        return $scheduledCommand;
    }

    private function save(ScheduledCommand $command): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::$container->get(EntityManagerInterface::class);
        $em->persist($command);
        $em->flush();
    }
}
