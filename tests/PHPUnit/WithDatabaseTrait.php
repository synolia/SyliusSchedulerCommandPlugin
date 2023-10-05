<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit;

use Doctrine\ORM\Tools\SchemaTool;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait WithDatabaseTrait
{
    public static function initDatabase(KernelInterface $kernel)
    {
        // Make sure we are in the test environment
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Primer must be executed in the test environment');
        }

        // Get the entity manager from the service container
        $entityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        // Run the schema update tool using our entity metadata
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadatas);

        /** @var SuiteRegistryInterface $suiteRegistry */
        $suiteRegistry = $kernel->getContainer()->get(SuiteRegistryInterface::class);
        $suite = $suiteRegistry->getSuite('default');

        /** @var SuiteLoaderInterface $suiteLoader */
        $suiteLoader = $kernel->getContainer()->get(SuiteLoaderInterface::class);
        $suiteLoader->load($suite);
    }
}
