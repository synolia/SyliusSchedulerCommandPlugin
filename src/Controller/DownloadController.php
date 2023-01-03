<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class DownloadController extends AbstractController
{
    public function __construct(
        private ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private string $logsDir,
    ) {
    }

    public function downloadLogFile(string $command): Response
    {
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduleCommand ||
            null === $scheduleCommand->getLogFile()
        ) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $filePath = $this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile();
        if (!\file_exists($filePath)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->file($filePath);
    }
}
