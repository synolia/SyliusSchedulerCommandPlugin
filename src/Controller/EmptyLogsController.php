<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class EmptyLogsController extends AbstractController
{
    /** @var ScheduledCommandRepository */
    private $scheduledCommandRepository;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string */
    private $logsDir;

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
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
            'sylius_admin_scheduled_command_index',
            [],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }
}
