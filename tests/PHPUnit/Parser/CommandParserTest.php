<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Parser;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Parser\CommandParserInterface;

final class CommandParserTest extends KernelTestCase
{
    public function testListsTheCommandsGroupedByNamespace(): void
    {
        $commands = self::getContainer()->get(CommandParserInterface::class)->getCommands();

        self::assertArrayHasKey('synolia', $commands);
        self::assertSame('synolia:scheduler-run', $commands['synolia']['synolia:scheduler-run'] ?? null);
    }

    public function testExcludesTheCompletionCommand(): void
    {
        $commands = self::getContainer()->get(CommandParserInterface::class)->getCommands();

        self::assertArrayNotHasKey('_complete', $commands);
    }
}
