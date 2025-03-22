<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Provider;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(DateTimeProviderInterface::class)]
final class CalendarWithTimezone implements DateTimeProviderInterface
{
    public function __construct(
        #[Autowire(param: 'env(SYNOLIA_SCHEDULER_PLUGIN_TIMEZONE)')]
        private ?string $timezone = null,
    ) {
    }

    /**
     * @throws Exception
     */
    public function now(): \DateTimeInterface
    {
        $timezone = $this->timezone ?? date_default_timezone_get();

        return new DateTimeImmutable(timezone: new DateTimeZone($timezone));
    }
}
