<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class ExecuteScheduleCommand
{
    /** @var ScheduledCommandRepository */
    private $scheduledCommandRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var KernelInterface */
    private $kernel;

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel
    ) {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    public function executeImmediate(string $command): bool
    {
        /** @var ScheduledCommand $scheduleCommand */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);
        if ($scheduleCommand === null) {
            return false;
        }

        $scheduleCommand->setExecuteImmediately(true);
        $this->entityManager->flush();

        $rootDir = $this->kernel->getRootDir();
        $process = new Process(["cd $rootDir && bin/console synolia:scheduler-run --id=$command"]);
        $process->start();

        return true;
    }
}
