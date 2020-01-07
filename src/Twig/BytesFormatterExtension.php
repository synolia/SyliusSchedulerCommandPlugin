<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class BytesFormatterExtension extends AbstractExtension
{
    private const EMPTY_FILE_SIZE = 0;

    public function getFilters(): array
    {
        return [
            new TwigFilter('format_bytes', [$this, 'formatBytes']),
        ];
    }

    public function formatBytes(int $bytes): string
    {
        try {
            $number = floor(log($bytes, 1024));

            return round($bytes / (1024 ** $number), [0, 2, 2, 2, 3][$number]) . ['B', 'kB', 'MB', 'GB', 'TB'][$number];
        } catch (\Throwable $throwable) {
            return '0B';
        }
    }
}
