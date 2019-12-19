<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class DownloadController extends AbstractController
{
    /** @var ScheduledCommandRepository */
    private $scheduledCommandRepository;

    public function __construct(ScheduledCommandRepository $scheduledCommandRepository)
    {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
    }

    public function downloadLogFile(string $command): Response
    {
        /** @var ScheduledCommand $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduleCommand->getLogFile() ||
            null === $this->getParameter('kernel.logs_dir')
        ) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $filePath = $this->getParameter('kernel.logs_dir') . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile();
        if (!\file_exists($filePath)) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->file($filePath);
    }
}
