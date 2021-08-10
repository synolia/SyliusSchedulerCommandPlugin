<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Sivaschenko\Utility\Cron\ExpressionFactory;
use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class ScheduledCommandHumanReadableExpressionType implements FieldTypeInterface
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        if (!\class_exists(ExpressionFactory::class) || '' === $scheduleCommand->getCronExpression()) {
            return $scheduleCommand->getCronExpression();
        }

        try {
            $expression = ExpressionFactory::getExpression($scheduleCommand->getCronExpression());

            return $this->twig->render(
                $options['template'],
                [
                    'schedulerCommand' => $scheduleCommand,
                    'value' => $expression->getVerbalString(),
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
}
