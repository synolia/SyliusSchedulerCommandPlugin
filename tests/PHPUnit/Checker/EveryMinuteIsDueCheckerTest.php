<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Checker;

use Synolia\SyliusSchedulerCommandPlugin\Checker\EveryMinuteIsDueChecker;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\AbstractIsDueTestCase;

class EveryMinuteIsDueCheckerTest extends AbstractIsDueTestCase
{
    /**
     * @dataProvider isDueUsingCronExpressionDataProvider
     */
    public function testIsDue(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        bool $expectedResult,
    ): void {
        $checker = new EveryMinuteIsDueChecker();
        $command = $this->setupCommand($cronExpression);

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $checker->isDue($command, $currentDateTime));
    }
}
