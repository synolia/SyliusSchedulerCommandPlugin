<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit;

use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

abstract class AbstractIsDueTest extends KernelTestCase
{
    use WithDatabaseTrait;

    /** @var EntityManagerInterface */
    protected $entityManager;

    public function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        self::initDatabase($kernel);

        $this->entityManager = self::$container->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    protected function setupCommand(string $cronExpression): CommandInterface
    {
        $command = new Command();
        $command
            ->setCronExpression($cronExpression)
        ;
        $this->entityManager->persist($command);

        return $command;
    }

    public function isDueUsingCronExpressionDataProvider(): \Generator
    {
        yield 'Every minute - now' => ['* * * * *', new \DateTime(), true];
        yield 'Every minute - now minus 1 minute' => ['* * * * *', (new \DateTime())->sub(new DateInterval('PT1M')), true];
        yield 'Every minute - now plus 1 minute' => ['* * * * *', (new \DateTime())->add(new DateInterval('PT1M')), true];

        yield 'At 1AM - now' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:00'), true];
        yield 'At 1AM - now minus 1 minute' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 00:59'), false];
        yield 'At 1AM - now plus 1 minute' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:01'), false];
    }

    public function isDueWithoutScheduledHistoryDataProvider(): \Generator
    {
        $today = new \DateTime();

        yield 'Without history, every 10 min - 2021-08-01 01:00' => ['*/10 * * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:00'), true];
        yield 'Without history, every 10 min - 2021-08-01 01:10' => ['*/10 * * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:10'), true];
        yield 'Without history, every 10 min - 2021-08-01 01:15' => ['*/10 * * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:15'), false];
        yield 'Without history, every 10 min - 2021-08-01 01:20' => ['*/10 * * * *', \DateTime::createFromFormat('Y-m-d H:i', '2021-08-01 01:20'), true];
        yield 'Without history, at 1AM 2021-08-01 01:01' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:01', $today->format('Y-m-d'))), true];
        yield 'Without history, at 1AM 2021-08-01 01:02' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:02', $today->format('Y-m-d'))), true];
        yield 'Without history, at 1AM 2021-08-01 01:03' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:03', $today->format('Y-m-d'))), true];
        yield 'Without history, at 1AM 2021-08-01 01:04' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:04', $today->format('Y-m-d'))), true];
        yield 'Without history, at 1AM 2021-08-01 01:05' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:05', $today->format('Y-m-d'))), true];
        yield 'Without history, at 1AM 2021-08-01 01:06' => ['0 1 * * *', \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:06', $today->format('Y-m-d'))), false];
    }

    public function isDueWithScheduledHistoryDataProvider(): \Generator
    {
        $today = new \DateTime();

        yield 'With history in the same minute, at 1:07AM - 2021-08-01 01:01' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            true,
        ];
        yield 'With history for the least 5 minutes, at 1:07AM - 2021-08-01 01:08' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:08', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at 1:07AM - 2021-08-01 01:09' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:09', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at 1:07AM - 2021-08-01 01:10' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:10', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at 1:07AM - 2021-08-01 01:11' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:11', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at 1:07AM - 2021-08-01 01:12' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:12', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at every 10 minutes - 2021-08-01 01:08' => [
            '*/10 * * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:08', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With history for the least 5 minutes, at every 10 minutes - 2021-08-01 01:09' => [
            '*/10 * * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:09', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With no history for the least 5 minutes, at every 10 minutes - 2021-08-01 01:10' => [
            '*/10 * * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:10', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'With no history for the least 5 minutes, at 1:07AM - 2021-08-01 01:06' => [
            '0 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:06', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'With no-history for the least 5 minutes, at 1:05AM - 2021-08-01 01:06' => [
            '5 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:06', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:00', $today->format('Y-m-d'))),
            true,
        ];

        /*
         * CONFIGURATION SCENARIO: cron set to be run at 01:07 in the scheduler command plugin
         *
         * SCENARIO CASES AT 1 CRON PASS EVERY 5 MINUTES FROM THE PROVIDER
         * cron passes at 01:04 - 1..5: false
         * cron passes at 01:05 - 1..5: false
         * cron passes at 01:06 - 1..5: false
         * cron passes at 01:07 - 1..5 : true (but never checked as it is already handled by EveryMinuteIsDueChecker)
         * cron passes at 01:08 - 1..5 : true
         * cron passes at 01:09 - 1..5 : true #should not if another has started during the threshold period
         * cron passes at 01:10 - 1..5 : true #should not if another has started during the threshold period
         * cron passes at 01:11 - 1..5 : true #should not if another has started during the threshold period
         * cron passes at 01:12 - 1..5 : true #should not if another has started during the threshold period
         * cron passes at 01:13 - 1..5 : false
         */
        yield 'cron passes at 01:04 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:04', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:05 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:05', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:06 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:06', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:07 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:08 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:08', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:09 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:09', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:10 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:10', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:11 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:11', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:12 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:12', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:13 - 1..5 without cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:13', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 00:00', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:07 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            true,
        ];
        yield 'cron passes at 01:08 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:08', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:09 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:09', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:10 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:10', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:11 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:11', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:12 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:12', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
        yield 'cron passes at 01:13 - 1..5 but a cron already passed in the soft limit threshold' => [
            '7 1 * * *',
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:13', $today->format('Y-m-d'))),
            \DateTime::createFromFormat('Y-m-d H:i', sprintf('%s 01:07', $today->format('Y-m-d'))),
            false,
        ];
    }
}
