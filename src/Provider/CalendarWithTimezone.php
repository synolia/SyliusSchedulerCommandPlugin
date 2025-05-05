<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Provider;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Psr\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(ClockInterface::class)]
final class CalendarWithTimezone implements ClockInterface
{
    public function __construct(
        #[Autowire(param: 'env(SYNOLIA_SCHEDULER_PLUGIN_TIMEZONE)')]
        private ?string $timezone = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function now(): DateTimeImmutable
    {
        $timezone = $this->timezone ?? date_default_timezone_get();

        return new DateTimeImmutable(timezone: new DateTimeZone($timezone));
    }
}
