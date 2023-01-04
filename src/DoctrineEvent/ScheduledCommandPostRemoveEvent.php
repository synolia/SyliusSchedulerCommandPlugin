<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\DoctrineEvent;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;

class ScheduledCommandPostRemoveEvent implements EventSubscriber
{
    public function __construct(private string $logsDir)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postRemove,
        ];
    }

    public function postRemove(LifecycleEventArgs $eventArgs): void
    {
        $scheduledCommand = $eventArgs->getEntity();

        if (!$scheduledCommand instanceof ScheduledCommandInterface) {
            return;
        }

        if (null === $scheduledCommand->getLogFile() || '' === $scheduledCommand->getLogFile()) {
            return;
        }

        $filePath = $this->logsDir . \DIRECTORY_SEPARATOR . $scheduledCommand->getLogFile();

        if (!\file_exists($filePath)) {
            return;
        }

        @unlink($filePath);
    }
}
