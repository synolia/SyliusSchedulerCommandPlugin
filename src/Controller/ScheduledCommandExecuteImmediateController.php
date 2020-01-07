<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SchedulerCommandPlugin\Service\ExecuteScheduleCommand;

class ScheduledCommandExecuteImmediateController extends AbstractController
{
    /** @var ExecuteScheduleCommand */
    private $executeScheduleCommand;

    public function __construct(ExecuteScheduleCommand $executeScheduleCommand)
    {
        $this->executeScheduleCommand = $executeScheduleCommand;
    }

    public function executeImmediate(string $commandId): Response
    {
        $this->executeScheduleCommand->executeImmediate($commandId);

        return $this->redirectToRoute('sylius_admin_scheduled_command_index');
    }
}
