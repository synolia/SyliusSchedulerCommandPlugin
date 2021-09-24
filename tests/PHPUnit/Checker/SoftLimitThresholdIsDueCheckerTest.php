<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Checker;

use Synolia\SyliusSchedulerCommandPlugin\Checker\SoftLimitThresholdIsDueChecker;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\AbstractIsDueTest;

class SoftLimitThresholdIsDueCheckerTest extends AbstractIsDueTest
{
    /** @var SoftLimitThresholdIsDueChecker */
    private $softLimitThresholdIsDueChecker;

    public function setUp(): void
    {
        parent::setUp();
        $this->softLimitThresholdIsDueChecker = self::$container->get(SoftLimitThresholdIsDueChecker::class);
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

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $this->softLimitThresholdIsDueChecker->isDue($command, $currentDateTime));
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

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $this->softLimitThresholdIsDueChecker->isDue($command, $currentDateTime));
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

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $this->softLimitThresholdIsDueChecker->isDue($command, $currentDateTime));
    }
}
