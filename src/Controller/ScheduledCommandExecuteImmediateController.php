<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Repository\ScheduledCommandRepository;

class ScheduledCommandExecuteImmediateController extends AbstractController
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

    public function ExecuteImmediate(string $command): Response
    {
        /** @var ScheduledCommand $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);
        $scheduleCommand->setExecuteImmediately(true);
        $this->entityManager->flush();

        $rootDir = $this->kernel->getRootDir();
        $process = new Process("cd $rootDir && bin/console synolia:scheduler-run --id=$command");
        $process->start();

        return $this->redirectToRoute('sylius_admin_scheduled_command_index');
    }
}