<?php

declare(strict_types=1);

namespace Tests\Synolia\SchedulerCommandPlugin\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Factory\Factory;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SchedulerCommandPlugin\Factory\SchedulerFactory;
use Synolia\SchedulerCommandPlugin\Factory\SchedulerFactoryInterface;

final class SchedulerFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $schedulerCommand = (new Factory(ScheduledCommand::class))->createNew();
        $this->assertInstanceOf(ScheduledCommandInterface::class, $schedulerCommand);
    }
}
