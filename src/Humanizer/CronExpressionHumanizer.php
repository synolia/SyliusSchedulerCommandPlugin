<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Humanizer;

use Lorisleiva\CronTranslator\CronTranslator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;

final class CronExpressionHumanizer implements HumanizerInterface
{
    /** @var \Sylius\Component\Locale\Context\LocaleContextInterface */
    private $localeContext;

    public function __construct(LocaleContextInterface $localeContext)
    {
        $this->localeContext = $localeContext;
    }

    public function humanize(string $expression): string
    {
        if (!class_exists(CronTranslator::class) || '' === $expression) {
            return $expression;
        }

        $locale = $this->getLocale();

        try {
            return CronTranslator::translate($expression, $locale);
        } catch (\Throwable $throwable) {
            return $expression;
        }
    }

    private function getLocale(): string
    {
        try {
            $locale = $this->localeContext->getLocaleCode();

            if (2 === mb_strlen($locale)) {
                return $locale;
            }

            return mb_substr($locale, 0, 2);
        } catch (LocaleNotFoundException $localeNotFoundException) {
            return 'en';
        }
    }
}
