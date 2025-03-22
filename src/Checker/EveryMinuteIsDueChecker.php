<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Cron\CronExpression;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

/**
 * This checker only works if current date/time is checked every minutes
 */
#[AutoconfigureTag(IsDueCheckerInterface::class)]
class EveryMinuteIsDueChecker implements IsDueCheckerInterface
{
    private const PRIORITY = 0;

    public function __construct(
        private readonly ?DateTimeProviderInterface $dateTimeProvider = null,
    ) {
        if (!$dateTimeProvider instanceof DateTimeProviderInterface) {
            trigger_deprecation(
                'synolia/sylius-scheduler-command-plugin',
                '3.9',
                'Not passing a service that implements "%s" as a 1st argument of "%s" constructor is deprecated and will be prohibited in 4.0.',
                DateTimeProviderInterface::class,
                self::class,
            );
        }
    }

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    /**
     * @throws IsNotDueException
     */
    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        if (!$dateTime instanceof \DateTimeInterface) {
            $dateTime = $this->dateTimeProvider?->now() ?? new \DateTime();
        }

        $cron = new CronExpression($command->getCronExpression());

        if (!$cron->isDue($dateTime)) {
            throw new IsNotDueException();
        }

        return true;
    }
}
