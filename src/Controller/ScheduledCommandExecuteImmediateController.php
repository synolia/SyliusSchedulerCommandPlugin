<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner;
use Webmozart\Assert\Assert;

class ScheduledCommandExecuteImmediateController extends AbstractController
{
    /** @var \Synolia\SyliusSchedulerCommandPlugin\Service\ScheduledCommandPlanner */
    private $scheduledCommandPlanner;

    /** @var \Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepositoryInterface */
    private $commandRepository;

    /** @var \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface */
    private $flashBag;

    public function __construct(
        ScheduledCommandPlanner $scheduledCommandPlanner,
        CommandRepositoryInterface $commandRepository,
        FlashBagInterface $flashBag
    ) {
        $this->scheduledCommandPlanner = $scheduledCommandPlanner;
        $this->commandRepository = $commandRepository;
        $this->flashBag = $flashBag;
    }

    public function executeImmediate(string $commandId): Response
    {
        $command = $this->commandRepository->find($commandId);
        Assert::isInstanceOf($command, CommandInterface::class);

        $scheduledCommand = $this->scheduledCommandPlanner->plan($command);

        $this->flashBag->add('success', \sprintf(
            'Command "%s" as been planned for execution.',
            $scheduledCommand->getName(),
        ));

        return $this->redirectToRoute('synolia_admin_command_index');
    }
}
