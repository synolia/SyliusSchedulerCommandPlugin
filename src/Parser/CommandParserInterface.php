<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Parser;

interface CommandParserInterface
{
    public function getCommands(): array;
}
