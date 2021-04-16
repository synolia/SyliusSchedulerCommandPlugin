<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\DependencyInjection;

use Sivaschenko\Utility\Cron\ExpressionFactory;
use Sylius\Bundle\CoreBundle\DependencyInjection\PrependDoctrineMigrationsTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SynoliaSyliusSchedulerCommandExtension extends Extension implements PrependExtensionInterface
{
    use PrependDoctrineMigrationsTrait;

    private const GRID_KEY = 'sylius_admin_scheduled_command';

    /** @var array */
    private $gridConf;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $this->gridConf = $container->getExtensionConfig('sylius_grid');

        $this->removeHumanizedCronExpressionColumn($container);
        $this->prependDoctrineMigrations($container);
    }

    private function removeHumanizedCronExpressionColumn(ContainerBuilder $container): void
    {
        if (class_exists(ExpressionFactory::class)) {
            return;
        }
        $index = $this->findSchedulerCommandGridIndex();

        unset($this->gridConf[$index]['grids'][self::GRID_KEY]['fields']['humanReadableExpression']);

        $container->prependExtensionConfig(
            'sylius_grid',
            $this->gridConf[$index]
        );
    }

    private function findSchedulerCommandGridIndex(): int
    {
        foreach ($this->gridConf as $key => $configuration) {
            if (!isset($configuration['grids'])) {
                continue;
            }

            if (!isset($configuration['grids'][self::GRID_KEY])) {
                continue;
            }

            return $key;
        }

        throw new \LogicException('SchedulerCommand grid not found.');
    }

    protected function getMigrationsNamespace(): string
    {
        return 'Synolia\SyliusSchedulerCommandPlugin\Migrations';
    }

    protected function getMigrationsDirectory(): string
    {
        return '@SynoliaSyliusSchedulerCommandPlugin/Migrations';
    }

    protected function getNamespacesOfMigrationsExecutedBefore(): array
    {
        return [];
    }
}
