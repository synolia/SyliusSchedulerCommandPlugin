<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SyliusSchedulerCommandPlugin\Humanizer\HumanizerInterface;
use Twig\Environment;

final class ScheduledCommandHumanReadableExpressionType implements FieldTypeInterface
{
    private \Twig\Environment $twig;

    private \Synolia\SyliusSchedulerCommandPlugin\Humanizer\HumanizerInterface $humanizer;

    public function __construct(
        Environment $twig,
        HumanizerInterface $humanizer,
    ) {
        $this->twig = $twig;
        $this->humanizer = $humanizer;
    }

    /**
     * @inheritdoc
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        return $this->twig->render(
            $options['template'],
            [
                'schedulerCommand' => $scheduleCommand,
                'value' => $this->humanizer->humanize($scheduleCommand->getCronExpression()),
            ],
        );
    }

    /** @inheritdoc */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
    }
}
