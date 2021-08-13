<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Humanizer;

interface HumanizerInterface
{
    public function humanize(string $expression): string;
}
