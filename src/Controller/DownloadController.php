<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class DownloadController extends AbstractController
{
    /** @var \Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    /** @var string */
    private $logsDir;

    public function __construct(ScheduledCommandRepositoryInterface $scheduledCommandRepository, string $logsDir)
    {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->logsDir = $logsDir;
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
