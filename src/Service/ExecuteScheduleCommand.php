<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

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

    public function executeImmediate(string $commandId): bool
    {
        /** @var ScheduledCommand $scheduleCommand */
        $scheduleCommand = $this->scheduledCommandRepository->find($commandId);
        if ($scheduleCommand === null) {
            return false;
        }

        $scheduleCommand->setExecuteImmediately(true);
        $this->entityManager->flush();

        $rootDir = $this->kernel->getProjectDir();
        $process = Process::fromShellCommandline("bin/console synolia:scheduler-run --id=$commandId", $rootDir);
        $process->start();

        return true;
    }
}
