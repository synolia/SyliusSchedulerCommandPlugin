<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

final class ScheduledCommandUrlType implements FieldTypeInterface
{
    /**
     * {@inheritdoc}
     *
     * @var ScheduledCommand $scheduleCommand
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        return '<a href="/admin/admin/scheduled-commands/download/logfile/' . $scheduleCommand->getId() . '">' . $scheduleCommand->getLogFile() . '<a/>';
    }

    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}