<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class DownloadController extends AbstractController
{
    /**
     * @var ScheduledCommandRepository
     */
    private $scheduledCommandRepository;

    public function __construct(ScheduledCommandRepository $scheduledCommandRepository)
    {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
    }

    public function downloadLogFile(string $command): BinaryFileResponse
    {
        /** @var ScheduledCommand $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        return $this->file($this->getParameter('kernel.logs_dir') . DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile());
    }
}
