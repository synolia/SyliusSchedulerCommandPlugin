<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Process\Process;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Planner\ScheduledCommandPlannerInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Webmozart\Assert\Assert;

class ScheduledCommandExecuteImmediateController extends AbstractController
{
    /** @var ScheduledCommandPlannerInterface */
    private $scheduledCommandPlanner;

    /** @var CommandRepositoryInterface */
    private $commandRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var string */
    private $projectDir;

    public function __construct(
        ScheduledCommandPlannerInterface $scheduledCommandPlanner,
        CommandRepositoryInterface $commandRepository,
        FlashBagInterface $flashBag,
        string $projectDir
    ) {
        $this->scheduledCommandPlanner = $scheduledCommandPlanner;
        $this->commandRepository = $commandRepository;
        $this->flashBag = $flashBag;
        $this->projectDir = $projectDir;
    }

    public function executeImmediate(string $commandId): Response
    {
        $command = $this->commandRepository->find($commandId);
        Assert::isInstanceOf($command, CommandInterface::class);

        $scheduledCommand = $this->scheduledCommandPlanner->plan($command);

        $this->executeFromCron($scheduledCommand);

        $this->flashBag->add('success', \sprintf(
            'Command "%s" as been planned for execution.',
            $scheduledCommand->getName(),
        ));

        return $this->redirectToRoute('synolia_admin_command_index');
    }

    public function executeFromCron(ScheduledCommandInterface $scheduledCommand): int
    {
        $process = Process::fromShellCommandline($this->getCommandLine($scheduledCommand));
        $process->setIdleTimeout(null);
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
