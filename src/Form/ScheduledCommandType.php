<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

final class ScheduledCommandType extends AbstractType
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('command', CommandChoiceType::class)
            ->add('arguments')
            ->add('cronExpression')
            ->add('logFile')
            ->add('priority')
            ->add('executeImmediately')
            ->add('disabled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScheduledCommand::class,
        ]);
    }
}
