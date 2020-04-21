<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    public function __construct(
        ScheduledCommandRepository $scheduledCommandRepository,
        LogDataRetriever $logDataRetriever,
        int $updateTime = 2000
    ) {
        $this->logDataRetriever = $logDataRetriever;
        $this->updateTime = $updateTime;
        $this->scheduledCommandRepository = $scheduledCommandRepository;
    }

    public function getLogs(Request $request, string $command): JsonResponse
    {
        /** @var ScheduledCommandInterface $command */
        $scheduleCommand = $this->scheduledCommandRepository->find($command);

        if (null === $scheduleCommand ||
            null === $scheduleCommand->getLogFile() ||
            null === $this->getParameter('kernel.logs_dir')
        ) {
            return new JsonResponse('', Response::HTTP_NO_CONTENT);
        }

        $baseLogDir = __DIR__ . '/../../tests/Application/var/log';

        if (true === (bool) $request->get('refresh')) {
            $result = $this->logDataRetriever->getLog(
                $baseLogDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile(),
                (int) $request->get('lastsize'),
                (string) $request->get('grep-keywords'),
                (bool) $request->get('invert')
            );

            return new JsonResponse([
                'size' => $result['size'],
                'data' => $result['data'],
            ]);
        }

        $result = $this->logDataRetriever->getLog($baseLogDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile());

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
            null === $scheduledCommand->getLogFile() ||
            null === $this->getParameter('kernel.logs_dir')
        ) {
            //TODO : Create error flashbag
            return $this->redirectToRoute('sylius_admin_scheduled_command_index');
        }

        return $this->render('@SynoliaSyliusSchedulerCommandPlugin/Resources/views/Controller/show.html.twig', [
            'route' => $this->generateUrl('sylius_admin_scheduler_get_log_file', ['command' => $command]),
            'updateTime' => $this->updateTime,
            'scheduledCommand' => $scheduledCommand,
        ]);
    }
}
