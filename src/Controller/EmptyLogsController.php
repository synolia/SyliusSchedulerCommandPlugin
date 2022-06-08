<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

final class EmptyLogsController extends AbstractController
{
    private ScheduledCommandRepositoryInterface $scheduledCommandRepository;

    private TranslatorInterface $translator;

    private string $logsDir;

    public function __construct(
        ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        TranslatorInterface $translator,
        string $logsDir
    ) {
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->logsDir = $logsDir;
        $this->translator = $translator;
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

        $this->addFlash('success', $this->translator->trans('sylius.ui.scheduled_command.bulk_empty_logs'));

        return $this->redirectToRoute(
            'synolia_admin_command_index',
            [],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }
}
