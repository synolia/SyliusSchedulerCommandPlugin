<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

final class CommandType extends AbstractType
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
            ->add('enabled')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommandInterface::class,
        ]);
    }
}
