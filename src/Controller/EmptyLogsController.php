<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class EmptyLogsController extends AbstractController
{
    /** @var ScheduledCommandRepository */
    private $scheduledCommandRepository;

    /** @var string */
    private $logsDir;

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
        string $logsDir
    ) {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->logsDir = $logsDir;
    }

    public function emptyLogs(Request $request): Response
    {
        $commandIds = $request->get('ids');

        foreach ($commandIds as $commandId) {
            $command = $this->scheduledCommandRepository->find($commandId);
            if ($command !== null && $command->getLogFile() !== null) {
                @\file_put_contents($this->logsDir . \DIRECTORY_SEPARATOR . $command->getLogFile(), '');
            }
        }

        return $this->redirectToRoute('sylius_admin_scheduled_command_index');
    }
}
