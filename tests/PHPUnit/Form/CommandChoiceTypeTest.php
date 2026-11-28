<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Form;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Form\CommandChoiceType;

final class CommandChoiceTypeTest extends KernelTestCase
{
    public function testOffersTheApplicationCommandsAsChoices(): void
    {
        $form = self::getContainer()->get(FormFactoryInterface::class)->create(CommandChoiceType::class);

        $choices = $form->getConfig()->getOption('choices');

        self::assertSame('synolia:scheduler-run', $choices['synolia']['synolia:scheduler-run'] ?? null);
    }
}
