<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class DownloadController extends AbstractController
{
    /**
     * @var ScheduledCommandRepository
     */
    private $scheduledCommandRepository;

    public function __construct(ScheduledCommandRepository $scheduledCommandRepository)
    {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
    }

    public function downloadLogFile(string $command)
    {
        /** @var ScheduledCommand $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        return $this->file($this->getParameter('kernel.project_dir') . '/var/log/' . $scheduleCommand->getLogFile());
    }
}