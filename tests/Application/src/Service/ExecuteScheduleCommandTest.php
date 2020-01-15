<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Application\src\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Service\ExecuteScheduleCommand;

class ExecuteScheduleCommandTest extends WebTestCase
{
    /** @var ExecuteScheduleCommand */
    private $excecuteScheduleCommand;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->excecuteScheduleCommand = $this->getContainer()->get(ExecuteScheduleCommand::class);
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
    }

    public function getContainer(): ContainerInterface
    {
        if (null === self::$container) {
            self::bootKernel();
        }

        return self::$container;
    }

    public function testExecuteImmediteWithWrongCommand(): void
    {
        $commandResult = $this->excecuteScheduleCommand->executeImmediate('hello world !');

        $this->assertEquals(false, $commandResult);
    }

    public function testExecuteImmediteWithGoodCommand(): void
    {
        /** @var ScheduledCommand $result */
        $result = $this->entityManager->getRepository(ScheduledCommand::class)->findOneBy(['name' => 'Reset Sylius']);

        $commandResult = $this->excecuteScheduleCommand->executeImmediate((string) $result->getId());

        $this->assertEquals(true, $commandResult);
    }
}
