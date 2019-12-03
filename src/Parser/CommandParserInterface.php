<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Parser;

interface CommandParserInterface
{
    public function getCommands(): array;
}
