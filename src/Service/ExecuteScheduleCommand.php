<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class ExecuteScheduleCommand
{
    /** @var ScheduledCommandRepository */
    private $scheduledCommandRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var KernelInterface */
    private $kernel;

    /** @var string */
    private $logsDir;

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
        string $logsDir
    ) {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
        $this->logsDir = $logsDir;
    }

    public function executeImmediate(string $commandId): bool
    {
        /** @var ScheduledCommand|null $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandRepository->find($commandId);
        if (!$scheduledCommand instanceof ScheduledCommand) {
            return false;
        }

        $process = Process::fromShellCommandline(
            $this->getCommandLine($scheduledCommand),
            $this->kernel->getProjectDir()
        );

        $scheduledCommand->setLastExecution(new \DateTime());
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
            'bin/console %s %s',
            $scheduledCommand->getCommand(),
            $scheduledCommand->getArguments() ?? ''
        );

        $logOutput = $this->getLogOutput($scheduledCommand);
        if (null !== $logOutput) {
            $commandLine = sprintf(
                'bin/console %s %s >> %s 2>> %s',
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments() ?? '',
                $logOutput,
                $logOutput
            );
        }

        return $commandLine;
    }
}
