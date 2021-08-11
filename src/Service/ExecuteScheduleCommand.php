<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

class ExecuteScheduleCommand
{
    /** @var ScheduledCommandRepositoryInterface */
    private $scheduledCommandRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    private $logsDir;

    /** @var string */
    private $projectDir;

    public function __construct(
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        string $logsDir,
        string $projectDir
    ) {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->logsDir = $logsDir;
        $this->projectDir = $projectDir;
    }

    public function executeImmediate(string $scheduledCommandId): bool
    {
        /** @var ScheduledCommandInterface|null $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandRepository->find($scheduledCommandId);
        if (!$scheduledCommand instanceof ScheduledCommandInterface) {
            return false;
        }

        $process = Process::fromShellCommandline(
            $this->getCommandLine($scheduledCommand),
            $this->kernel->getProjectDir()
        );

        $scheduledCommand->setExecutedAt(new \DateTime());
        $process->run();
        $result = $process->getExitCode();

        if (null === $result) {
            $result = 0;
        }

        $scheduledCommand->setLastReturnCode($result);
        $scheduledCommand->setCommandEndTime(new \DateTime());
        $this->entityManager->flush();

        return true;
    }

    public function executeFromCron(ScheduledCommandInterface $scheduledCommand): int
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->run();
        $result = $process->getExitCode();
        $scheduledCommand->setCommandEndTime(new \DateTime());

        if (null === $result) {
            $result = 0;
        }

        return $result;
    }

    private function getLogOutput(ScheduledCommandInterface $scheduledCommand): ?string
    {
        if ($scheduledCommand->getLogFile() === null || $scheduledCommand->getLogFile() === '') {
            return null;
        }

        return $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile();
    }

    private function getCommandLine(ScheduledCommandInterface $scheduledCommand): string
    {
        $commandLine = sprintf(
            '%s/bin/console %s %s',
            $this->projectDir,
            $scheduledCommand->getCommand(),
            $scheduledCommand->getArguments() ?? ''
        );

        $logOutput = $this->getLogOutput($scheduledCommand);
        if (null !== $logOutput) {
            $commandLine = sprintf(
                '%s/bin/console %s %s >> %s 2>> %s',
                $this->projectDir,
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments() ?? '',
                $logOutput,
                $logOutput
            );
        }

        return $commandLine;
    }
}
