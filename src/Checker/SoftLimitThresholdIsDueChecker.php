<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Checker;

use Cron\CronExpression;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Sylius\Calendar\Provider\DateTimeProviderInterface;
use Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;
use Synolia\SyliusSchedulerCommandPlugin\Repository\ScheduledCommandRepositoryInterface;

#[AutoconfigureTag(IsDueCheckerInterface::class)]
class SoftLimitThresholdIsDueChecker implements IsDueCheckerInterface
{
    private const PRIORITY = -100;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function __construct(
        private readonly ScheduledCommandRepositoryInterface $scheduledCommandRepository,
        private readonly ?DateTimeProviderInterface $dateTimeProvider = null,
        /**
         * Threshold in minutes
         */
        private readonly int $threshold = 5,
    ) {
        if (null === $dateTimeProvider) {
            trigger_deprecation(
                'synolia/sylius-scheduler-command-plugin',
                '3.9',
                'Not passing a service that implements "%s" as a 1st argument of "%s" constructor is deprecated and will be prohibited in 4.0.',
                DateTimeProviderInterface::class,
                self::class,
            );
        }
    }

    /**
     * @throws \Synolia\SyliusSchedulerCommandPlugin\Components\Exceptions\Checker\IsNotDueException
     */
    public function isDue(CommandInterface $command, ?\DateTimeInterface $dateTime = null): bool
    {
        if (!$dateTime instanceof \DateTimeInterface) {
            $dateTime = $this->dateTimeProvider?->now() ?? new \DateTime();
        }

        $cron = new CronExpression($command->getCronExpression());
        if ($cron->isDue($dateTime)) {
            return true;
        }

        $previousRunDate = $cron->getPreviousRunDate();
        $previousRunDateThreshold = (clone $previousRunDate)->add(new \DateInterval(\sprintf('PT%dM', $this->threshold)));

        $lastCreatedScheduledCommand = $this->scheduledCommandRepository->findLastCreatedCommand($command);

        // if never, do my command is valid for the least "threshold" minutes
        if (!$lastCreatedScheduledCommand instanceof \Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommandInterface) {
            if ($dateTime->getTimestamp() >= $previousRunDate->getTimestamp() && $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()) {
                return true;
            }

            throw new IsNotDueException();
        }

        // check if last command has been started since scheduled datetime +0..5 minutes
        if (
            $lastCreatedScheduledCommand->getCreatedAt()->getTimestamp() >= $previousRunDate->getTimestamp() &&
            $lastCreatedScheduledCommand->getCreatedAt()->getTimestamp() <= $previousRunDateThreshold->getTimestamp() &&
            $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()
        ) {
            throw new IsNotDueException();
        }

        if (
            $dateTime->getTimestamp() >= $previousRunDate->getTimestamp() &&
            $dateTime->getTimestamp() <= $previousRunDateThreshold->getTimestamp()
        ) {
            return true;
        }

        throw new IsNotDueException();
    }
}
