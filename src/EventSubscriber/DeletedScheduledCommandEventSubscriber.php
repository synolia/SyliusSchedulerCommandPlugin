<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\EventSubscriber;

use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

final class DeletedScheduledCommandEventSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $logsDir;

    public function __construct(string $logsDir)
    {
        $this->logsDir = $logsDir;
    }

    public static function getSubscribedEvents(): array
    {
        return ['synolia.scheduled_command.pre_delete' => ['deleteLogFile']];
    }

    public function deleteLogFile(ResourceControllerEvent $event): void
    {
        $scheduledCommand = $event->getSubject();

        if (!$scheduledCommand instanceof ScheduledCommandInterface) {
            return;
        }

        if (null === $scheduledCommand->getLogFile() || '' === $scheduledCommand->getLogFile()) {
            return;
        }

        $filePath = $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile();

        if (!file_exists($filePath)) {
            return;
        }

        @unlink($filePath);
    }
}
