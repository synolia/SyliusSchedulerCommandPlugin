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
    private $engine;

    public function __construct(Environment $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        if (!\class_exists(ExpressionFactory::class) || '' === $scheduleCommand->getCronExpression()) {
            return '';
        }

        try {
            $expression = ExpressionFactory::getExpression($scheduleCommand->getCronExpression());

            return $this->engine->render(
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
