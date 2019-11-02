<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Form;

use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduledCommandType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ScheduledCommand::class,
        ]);
    }
}
