<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Synolia\SchedulerCommandPlugin\Entity\ScheduledCommand;

final class ScheduledCommandUrlType implements FieldTypeInterface
{
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /** @var \Symfony\Component\Templating\EngineInterface */
    private $engine;

    public function __construct(UrlGeneratorInterface $urlGenerator, EngineInterface $engine)
    {
        $this->urlGenerator = $urlGenerator;
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     *
     * @var ScheduledCommand
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        $url = $this->urlGenerator->generate(
            'download_schedule_log_file',
            [
                'command' => $scheduleCommand->getId(),
            ]
        );

        return $this->engine->render(
            $options['template'],
            [
                'schedulerCommand' => $scheduleCommand,
                'url' => $url,
            ]
        );
    }

    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
    }
}
