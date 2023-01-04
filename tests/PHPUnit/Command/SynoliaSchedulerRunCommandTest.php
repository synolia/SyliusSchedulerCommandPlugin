<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

final class SynoliaSchedulerRunCommandTest extends KernelTestCase
{
    use WithDatabaseTrait;

    private static string $commandName = 'synolia:scheduler-run';

    private ?EntityManagerInterface $entityManager = null;

    public function setUp(): void
    {
        $kernel = static::bootKernel();
        self::initDatabase($kernel);

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
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

    public function testExecuteNonExistentCommand(): void
    {
        $invalidCommandName = 'non:existent';

        //remove last test
        /** @var CommandRepositoryInterface $repository */
        $repository = $this->entityManager->getRepository(ScheduledCommand::class);

        $lastScheduledCommand = $repository->findOneBy(['command' => $invalidCommandName]);
        if ($lastScheduledCommand !== null) {
            $repository->remove($lastScheduledCommand);
        }

        //generate command
        /** @var Command $command */
        $command = (new Factory(Command::class))->createNew();
        $command
            ->setCronExpression('* * * * *')
            ->setCommand('non:existent')
        ;

        $this->save($command);

        //run command
        $application = new Application(static::$kernel);

        $command = $application->find(self::$commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        //assertion
        self::assertStringContainsString('Cannot find non:existent', $commandTester->getDisplay());

        /** @var ScheduledCommand $persistedCommand */
        $persistedCommand = $repository->findOneBy(['command' => $invalidCommandName]);
        static::getContainer()->get(EntityManagerInterface::class)->refresh($persistedCommand);

        self::assertEquals(
            -1,
            $persistedCommand->getLastReturnCode(),
        );
    }

    private function generateAboutScheduleCommand(string $cron): CommandInterface
    {
        /** @var Command $command */
        $command = (new Factory(Command::class))->createNew();
        $command
            ->setCronExpression($cron)
            ->setCommand('about')
        ;

        return $command;
    }

    private function save(CommandInterface $command): void
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($command);
        $em->flush();
    }
}
