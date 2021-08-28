<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Checker;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Synolia\SyliusSchedulerCommandPlugin\Checker\EveryMinuteIsDueChecker;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;

class EveryMinuteIsDueCheckerTest extends TestCase
{
    /**
     * @dataProvider isDueDataProvider
     */
    public function testIsDue(
        string $cronExpression,
        \DateTimeInterface $currentDateTime,
        bool $expectedResult
    ): void {
        $checker = new EveryMinuteIsDueChecker();
        $command = new Command();
        $command->setCronExpression($cronExpression);

        if ($expectedResult === false) {
            $this->expectException(IsNotDueException::class);
        }

        $this->assertEquals($expectedResult, $checker->isDue($command, $currentDateTime));
    }

    public function isDueDataProvider(): \Generator
    {
        yield 'Every minute - now' => ['* * * * *', new \DateTime(), true];
        yield 'Every minute - now minus 1 minute' => ['* * * * *', (new \DateTime())->sub(new DateInterval('PT1M')), true];
        yield 'Every minute - now plus 1 minute' => ['* * * * *', (new \DateTime())->add(new DateInterval('PT1M')), true];

        yield 'At 1AM - now' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:00'), true];
        yield 'At 1AM - now minus 1 minute' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 00:59'), false];
        yield 'At 1AM - now plus 1 minute' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:01'), false];
    }
}
