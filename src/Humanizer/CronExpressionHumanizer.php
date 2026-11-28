<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Humanizer;

use Lorisleiva\CronTranslator\CronTranslator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CronExpressionHumanizer implements HumanizerInterface
{
    private const LANGUAGE_CODE_LENGTH = 2;

    public function __construct(
        private readonly LocaleContextInterface $localeContext,
        #[Autowire(param: 'env(bool:SYNOLIA_SCHEDULER_PLUGIN_TIMEFORMAT_24H)')]
        private readonly bool $timeFormat24Hours = false,
    ) {
    }

    public function humanize(string $expression): string
    {
        if (!\class_exists(CronTranslator::class) || '' === $expression) {
            return $expression;
        }

        $locale = $this->getLocale();

        try {
            return CronTranslator::translate($expression, $locale, $this->timeFormat24Hours);
        } catch (\Throwable) {
            return $expression;
        }
    }

    private function getLocale(): string
    {
        try {
            $locale = $this->localeContext->getLocaleCode();

            if (\strlen($locale) === self::LANGUAGE_CODE_LENGTH) {
                return $locale;
            }

            return mb_substr($locale, 0, self::LANGUAGE_CODE_LENGTH);
        } catch (LocaleNotFoundException) {
            return 'en';
        }
    }
}
