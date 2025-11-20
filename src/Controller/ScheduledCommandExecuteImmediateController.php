<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Webmozart\Assert\Assert;

class ScheduledCommandExecuteImmediateController extends AbstractController
{
    public function __construct(
        private readonly ScheduledCommandPlannerInterface $scheduledCommandPlanner,
        private readonly CommandRepositoryInterface $commandRepository,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
    ) {
    }

    #[Route('/scheduled-commands/execute/immediate/{commandId}', name: 'execute_immediate_schedule', defaults: ['_sylius' => ['permission' => true]], methods: ['GET|PUT'])]
    public function executeImmediate(Request $request, string $commandId): Response
    {
        $command = $this->commandRepository->find($commandId);
        Assert::isInstanceOf($command, CommandInterface::class);

        $scheduledCommand = $this->scheduledCommandPlanner->plan($command);

        $this->launchSubprocess($scheduledCommand);

        $request->getSession()->getFlashBag()->add('success', \sprintf(
            'Command "%s" as been planned for execution.',
            $scheduledCommand->getName(),
        ));

        return $this->redirectToRoute('synolia_admin_command_index');
    }

    private function launchSubprocess(ScheduledCommandInterface $scheduledCommand): void
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->setTimeout($scheduledCommand->getTimeout());
        $process->setIdleTimeout($scheduledCommand->getIdleTimeout());
        $process->start();
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
