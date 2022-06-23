<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Repository\CommandRepository;

final class CleanLogAction extends AbstractController
{
    public function __invoke(
        TranslatorInterface $translator,
        CommandRepository $commandRepository,
        string $command,
        string $logsDir
    ): Response {
        /** @var ScheduledCommand|null $scheduleCommand */
        $scheduleCommand = $commandRepository->find($command);

        if (null === $scheduleCommand) {
            $this->addFlash('error', $translator->trans('sylius.ui.scheduled_command_not_exists'));

            return $this->redirectToGrid();
        }

        if (null === $scheduleCommand->getLogFile()) {
            $this->addFlash('error', $translator->trans('sylius.ui.log_file_undefined'));

            return $this->redirectToGrid();
        }

        $filePath = $logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile();
        if (!\file_exists($filePath)) {
            $this->addFlash('error', $translator->trans('sylius.ui.no_log_file_found'));

            return $this->redirectToGrid();
        }

        try {
            file_put_contents($filePath, '');
        } catch (\Throwable $throwable) {
            $this->addFlash('error', $translator->trans('sylius.ui.error_emptying_log_file'));
        }

        $this->addFlash('success', $translator->trans('sylius.ui.log_file_successfully_emptied'));

        return $this->redirectToGrid();
    }

    private function redirectToGrid(): RedirectResponse
    {
        return $this->redirectToRoute(
            'synolia_admin_command_index',
            [],
            Response::HTTP_MOVED_PERMANENTLY
        );
    }
}
