<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusSchedulerCommandPlugin\Controller\DownloadController;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;

final class DownloadControllerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private string $logFilePath;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->beginTransaction();

        // SYNOLIA_SCHEDULER_PLUGIN_LOGS_DIR defaults to the kernel logs directory.
        $this->logFilePath = self::getContainer()->getParameter('kernel.logs_dir') . \DIRECTORY_SEPARATOR . \uniqid('download-test-', true) . '.log';
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        @\unlink($this->logFilePath);

        parent::tearDown();
    }

    public function testDownloadsTheLogFileOfTheScheduledCommand(): void
    {
        (new Filesystem())->dumpFile($this->logFilePath, "some output\n");
        $scheduledCommand = $this->createScheduledCommand(\basename($this->logFilePath));

        $response = self::getContainer()->get(DownloadController::class)->downloadLogFile((string) $scheduledCommand->getId());

        self::assertInstanceOf(BinaryFileResponse::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame($this->logFilePath, $response->getFile()->getPathname());
    }

    public function testReturnsNotFoundWhenTheScheduledCommandHasNoLogFile(): void
    {
        $scheduledCommand = $this->createScheduledCommand(null);

        $response = self::getContainer()->get(DownloadController::class)->downloadLogFile((string) $scheduledCommand->getId());

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsNotFoundWhenTheLogFileDoesNotExist(): void
    {
        $scheduledCommand = $this->createScheduledCommand(\basename($this->logFilePath));

        $response = self::getContainer()->get(DownloadController::class)->downloadLogFile((string) $scheduledCommand->getId());

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testReturnsNotFoundWhenTheScheduledCommandDoesNotExist(): void
    {
        $response = self::getContainer()->get(DownloadController::class)->downloadLogFile('0');

        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function createScheduledCommand(?string $logFile): ScheduledCommand
    {
        $scheduledCommand = (new ScheduledCommand())
            ->setName('Displays project information')
            ->setCommand('about')
            ->setLogFile($logFile)
        ;
        $this->entityManager->persist($scheduledCommand);
        $this->entityManager->flush();

        return $scheduledCommand;
    }
}
