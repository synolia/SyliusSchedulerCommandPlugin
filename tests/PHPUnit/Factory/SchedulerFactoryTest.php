<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Factory;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Resource\Factory\Factory;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Factory\SchedulerFactory;
use Synolia\SyliusSchedulerCommandPlugin\Factory\SchedulerFactoryInterface;

final class SchedulerFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $schedulerCommand = (new Factory(ScheduledCommand::class))->createNew();
        $this->assertInstanceOf(ScheduledCommandInterface::class, $schedulerCommand);
    }
}
