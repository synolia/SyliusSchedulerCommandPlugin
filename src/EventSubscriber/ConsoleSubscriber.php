<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\EventSubscriber;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Enum\ScheduledCommandStateEnum;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepository;

final class ConsoleSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ScheduledCommandRepository $scheduledCommandRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
            ConsoleEvents::SIGNAL => 'onConsoleSignal',
        ];
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event): void
    {
        $this->updateCommand($event);
    }

    public function onConsoleSignal(ConsoleSignalEvent $event): void
    {
        $this->updateCommand($event);
    }

    private function updateCommand(ConsoleSignalEvent|ConsoleTerminateEvent $event): void
    {
        try {
            $commandCode = $event->getCommand()?->getName() ?? 'no_command';
            /** @var ScheduledCommand|null $schedulerCommand */
            $schedulerCommand = $this->scheduledCommandRepository->findOneBy(['command' => $commandCode], ['id' => 'DESC']);
        } catch (\Throwable) {
            return;
        }

        if (null === $schedulerCommand) {
            return;
        }

        if ($schedulerCommand->getState() !== ScheduledCommandStateEnum::IN_PROGRESS) {
            return;
        }

        $exitCode = $event->getExitCode();
        if (false === $exitCode) {
            $exitCode = -1;
        }

        $schedulerCommand->setCommandEndTime(new \DateTime());
        $schedulerCommand->setState(ScheduledCommandStateEnum::TERMINATION);
        $schedulerCommand->setLastReturnCode($exitCode);
        $this->scheduledCommandRepository->add($schedulerCommand);
    }
}
