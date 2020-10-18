<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\DataRetriever\LogDataRetriever;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class LogViewerController extends AbstractController
{
    /** @var int The time in milliseconds between two AJAX requests to the server. */
    private $updateTime;

    /** @var \Synolia\SyliusSchedulerCommandPlugin\DataRetriever\LogDataRetriever */
    private $logDataRetriever;

    /** @var \Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository */
    private $scheduledCommandRepository;

    /** @var \Symfony\Contracts\Translation\TranslatorInterface */
    private $translator;

    /** @var string */
    private $logsDir;

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
        LogDataRetriever $logDataRetriever,
        TranslatorInterface $translator,
        string $logsDir,
        int $updateTime = 2000
    ) {
        $this->logDataRetriever = $logDataRetriever;
        $this->updateTime = $updateTime;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
        $this->translator = $translator;
        $this->logsDir = $logsDir;
    }

    public function getLogs(Request $request, string $command): JsonResponse
    {
        /** @var ScheduledCommandInterface $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduleCommand ||
            null === $scheduleCommand->getLogFile()
        ) {
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        if (true === (bool) $request->get('refresh')) {
            $result = $this->logDataRetriever->getLog(
                $this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile(),
                (int) $request->get('lastsize'),
                (string) $request->get('grep-keywords'),
                (bool) $request->get('invert')
            );

            return new JsonResponse([
                'size' => $result['size'],
                'data' => $result['data'],
            ]);
        }

        $result = $this->logDataRetriever->getLog($this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile());

        return new JsonResponse([
            'size' => $result['size'],
            'data' => $result['data'],
        ]);
    }

    public function show(string $command): Response
    {
        /** @var ScheduledCommandInterface $command */
        $scheduledCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduledCommand ||
            null === $scheduledCommand->getLogFile()
        ) {
            $this->addFlash('error', $this->translator->trans('sylius.ui.does_not_exists_or_missing_log_file'));

            return $this->redirectToRoute('sylius_admin_scheduled_command_index');
        }

        return $this->render('@SynoliaSyliusSchedulerCommandPlugin/Resources/views/Controller/show.html.twig', [
            'route' => $this->generateUrl('sylius_admin_scheduler_get_log_file', ['command' => $command]),
            'updateTime' => $this->updateTime,
            'scheduledCommand' => $scheduledCommand,
        ]);
    }
}
