<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Webmozart\Assert\Assert;

class ScheduledCommandExecuteImmediateController extends AbstractController
{
    private \Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface $scheduledCommandPlanner;

    private \Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface $commandRepository;

    private string $projectDir;

    public function __construct(
        ScheduledCommandPlannerInterface $scheduledCommandPlanner,
        CommandRepositoryInterface $commandRepository,
        string $projectDir,
    ) {
        $this->scheduledCommandPlanner = $scheduledCommandPlanner;
        $this->commandRepository = $commandRepository;
        $this->projectDir = $projectDir;
    }

    public function executeImmediate(Request $request, string $commandId): Response
    {
        $command = $this->commandRepository->find($commandId);
        Assert::isInstanceOf($command, CommandInterface::class);

        $scheduledCommand = $this->scheduledCommandPlanner->plan($command);

        $this->executeFromCron($scheduledCommand);

        $request->getSession()->getFlashBag()->add('success', \sprintf(
            'Command "%s" as been planned for execution.',
            $scheduledCommand->getName(),
        ));

        return $this->redirectToRoute('synolia_admin_command_index');
    }

    public function executeFromCron(ScheduledCommandInterface $scheduledCommand): int
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());
        $process->run();
        $result = $process->getExitCode();
        $scheduledCommand->setCommandEndTime(new \DateTime());

        if (null === $result) {
            $result = 0;
        }

        return $result;
    }

    private function getCommandLine(ScheduledCommandInterface $scheduledCommand): string
    {
        return sprintf(
            '%s/bin/console synolia:scheduler-run --id=%d > /dev/null 2>&1 &',
            $this->projectDir,
            $scheduledCommand->getId(),
        );
    }
}
