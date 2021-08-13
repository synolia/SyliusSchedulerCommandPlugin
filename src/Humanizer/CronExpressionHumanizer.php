<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Humanizer;

use Lorisleiva\CronTranslator\CronTranslator;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;

class CronExpressionHumanizer implements HumanizerInterface
{
    /** @var \Sylius\Component\Locale\Context\LocaleContextInterface */
    private $localeContext;

    public function __construct(LocaleContextInterface $localeContext)
    {
        $this->localeContext = $localeContext;
    }

    public function humanize(string $expression): string
    {
        if (!\class_exists(CronTranslator::class) || '' === $expression) {
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
            return $this->localeContext->getLocaleCode();
        } catch (LocaleNotFoundException $localeNotFoundException) {
            return 'en';
        }
    }
}
