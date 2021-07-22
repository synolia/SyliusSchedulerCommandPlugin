<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Service;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Factory\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\WithDatabaseTrait;

class ExecuteScheduleCommandTest extends WebTestCase
{
    use WithDatabaseTrait;

    /** @var ExecuteScheduleCommand */
    private $executeScheduleCommand;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        self::initDatabase($kernel);

        $this->executeScheduleCommand = self::$container->get(ExecuteScheduleCommand::class);
        $this->entityManager = self::$container->get(EntityManagerInterface::class);
    }

    public function testExecuteImmediateWithWrongCommand(): void
    {
        $commandResult = $this->executeScheduleCommand->executeImmediate('hello world !');

        $this->assertEquals(false, $commandResult);
    }

    public function testExecuteImmediateWithGoodCommand(): void
    {
        /** @var ScheduledCommand $scheduledCommand */
        $scheduledCommand = (new Factory(ScheduledCommand::class))->createNew();
        $scheduledCommand
            ->setName('About application')
            ->setCommand('about');

        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();

        /** @var ScheduledCommand $result */
        $result = $this->entityManager->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'About application']);
        $commandResult = $this->executeScheduleCommand->executeImmediate((string) $result->getId());

        $this->assertEquals(true, $commandResult);
    }
}
