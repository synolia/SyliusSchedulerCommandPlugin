<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Voter;

use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Voter\IsDueVoterInterface;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\AbstractIsDueTest;

class IsDueVoterTest extends AbstractIsDueTest
{
    /** @var \Synolia\SyliusSchedulerCommandPlugin\Voter\IsDueVoterInterface */
    private $voter;

    public function setUp(): void
    {
        parent::setUp();

        $this->voter = static::getContainer()->get(IsDueVoterInterface::class);
    }

    /**
     * @dataProvider isDueWithScheduledHistoryDataProvider
     */
    public function testIsDueWithScheduledHistory(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        \DateTimeInterface $lastCommandDateTime,
        bool $expectedResult
    ): void {
        $command = $this->setupCommand($cronExpression);

        $scheduledCommand = new ScheduledCommand();
        $reflectionClass = new \ReflectionClass($scheduledCommand);
        $reflectionProperty = $reflectionClass->getProperty('createdAt');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($scheduledCommand, $lastCommandDateTime);
        $this->entityManager->persist($scheduledCommand);
        $scheduledCommand->setOwner($command);

        $this->entityManager->flush();

        $this->assertEquals($expectedResult, $this->voter->isDue($command, $currentDateTime));
    }

    /**
     * @dataProvider isDueWithoutScheduledHistoryDataProvider
     */
    public function testIsDueWithoutScheduledHistory(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        bool $expectedResult
    ): void {
        $command = $this->setupCommand($cronExpression);

        $this->assertEquals($expectedResult, $this->voter->isDue($command, $currentDateTime));
    }

    /**
     * @dataProvider isDueUsingCronExpressionDataProvider
     */
    public function testIsDueUsingCronExpression(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        bool $expectedResult
    ): void {
        $command = $this->setupCommand($cronExpression);

        $this->assertEquals($expectedResult, $this->voter->isDue($command, $currentDateTime));
    }
}
