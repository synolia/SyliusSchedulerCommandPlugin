<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Lorisleiva\CronTranslator\CronTranslator;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class ScheduledCommandHumanReadableExpressionType implements FieldTypeInterface
{
    /** @var Environment */
    private $twig;

    /** @var \Sylius\Component\Locale\Context\LocaleContextInterface */
    private $localeContext;

    public function __construct(
        Environment $twig,
        LocaleContextInterface $localeContext
    ) {
        $this->twig = $twig;
        $this->localeContext = $localeContext;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        if (!\class_exists(CronTranslator::class) || '' === $scheduleCommand->getCronExpression()) {
            return $scheduleCommand->getCronExpression();
        }

        $locale = $this->getLocale();

        try {
            $expression = CronTranslator::translate($scheduleCommand->getCronExpression(), $locale);

            return $this->twig->render(
                $options['template'],
                [
                    'schedulerCommand' => $scheduleCommand,
                    'value' => $expression,
                ]
            );
        } catch (\Throwable $throwable) {
            return '';
        }
    }

    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
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
