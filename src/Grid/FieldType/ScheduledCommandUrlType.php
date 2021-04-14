<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Grid\FieldType;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\FieldTypes\FieldTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final class ScheduledCommandUrlType implements FieldTypeInterface
{
    /** @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface */
    private $urlGenerator;

    /** @var Environment */
    private $engine;

    /** @var string */
    private $logsDir;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Environment $engine,
        string $logsDir
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->engine = $engine;
        $this->logsDir = $logsDir;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Field $field, $scheduleCommand, array $options): string
    {
        $size = 0;

        $viewUrl = $this->urlGenerator->generate(
            'sylius_admin_scheduler_view_log_file',
            [
                'command' => $scheduleCommand->getId(),
            ]
        );

        $url = $this->urlGenerator->generate(
            'download_schedule_log_file',
            [
                'command' => $scheduleCommand->getId(),
            ]
        );

        $filePath = $this->logsDir . \DIRECTORY_SEPARATOR . $scheduleCommand->getLogFile();
        if (\file_exists($filePath)) {
            $size = filesize($filePath);
        }

        return $this->engine->render(
            $options['template'],
            [
                'schedulerCommand' => $scheduleCommand,
                'url' => $url,
                'viewUrl' => $viewUrl,
                'size' => $size,
            ]
        );
    }

    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('template');
    }
}
