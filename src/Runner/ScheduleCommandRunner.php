<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Runner;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

class ScheduleCommandRunner implements ScheduleCommandRunnerInterface
{
    public function __construct(private ScheduledCommandRepositoryInterface $scheduledCommandRepository, private EntityManagerInterface $entityManager, private KernelInterface $kernel, private string $logsDir, private string $projectDir, private int $pingInterval = 60, private bool $keepConnectionAlive = false)
    {
    }

    public function runImmediately(string $scheduledCommandId): bool
    {
        /** @var ScheduledCommandInterface|null $scheduledCommand */
        $scheduledCommand = $this->scheduledCommandRepository->find($scheduledCommandId);
        if (!$scheduledCommand instanceof ScheduledCommandInterface) {
            return false;
        }

        $process = Process::fromShellCommandline(
            $this->getCommandLine($scheduledCommand),
            $this->kernel->getProjectDir(),
        );

        $scheduledCommand->setExecutedAt(new \DateTime());
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());
        $this->startProcess($process);

        $result = $process->getExitCode();

        if (null === $result) {
            $result = 0;
        }

        $scheduledCommand->setLastReturnCode($result);
        $scheduledCommand->setCommandEndTime(new \DateTime());
        $this->entityManager->flush();

        return true;
    }

    public function runFromCron(ScheduledCommandInterface $scheduledCommand): int
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());

        try {
            $this->startProcess($process);
        } catch (ProcessTimedOutException) {
        }

        $result = $process->getExitCode();
        $scheduledCommand->setCommandEndTime(new \DateTime());

        if (null === $result) {
            $result = 0;
        }

        return $result;
    }

    private function startProcess(Process $process): void
    {
        if (!$this->keepConnectionAlive) {
            $process->run();

            return;
        }

        $process->start();
        while ($process->isRunning()) {
            $process->checkTimeout();

            try {
                $this->entityManager->getConnection()->executeQuery($this->entityManager->getConnection()->getDatabasePlatform()->getDummySelectSQL());
            } catch (\Doctrine\DBAL\Exception) {
            }

            for ($i = 0; $i < $this->pingInterval; ++$i) {
                if (!$process->isRunning()) {
                    return;
                }
                \sleep(1);

                $process->checkTimeout();
            }
        }
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
            $scheduledCommand->getArguments() ?? '',
        );

        $logOutput = $this->getLogOutput($scheduledCommand);
        if (null !== $logOutput) {
            $commandLine = sprintf(
                '%s/bin/console %s %s >> %s 2>> %s',
                $this->projectDir,
                $scheduledCommand->getCommand(),
                $scheduledCommand->getArguments() ?? '',
                $logOutput,
                $logOutput,
            );
        }

        return $commandLine;
    }
}
