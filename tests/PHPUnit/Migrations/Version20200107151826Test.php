<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Migrations;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Deprecations\Deprecation;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Exception\AbortMigration;
use Doctrine\Migrations\Version\Version;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Synolia\SyliusSchedulerCommandPlugin\Migrations\Version20200107151826;

final class Version20200107151826Test extends KernelTestCase
{
    /** AbstractPlatform::getName() is deprecated in DBAL 3 and removed in DBAL 4. */
    private const PLATFORM_NAME_DEPRECATION = 'https://github.com/doctrine/dbal/issues/4749';

    /** @dataProvider directionProvider */
    public function testRunsOnMysqlWithoutIdentifyingThePlatformByName(callable $migrate, string $expectedSql): void
    {
        $migration = $this->getRegisteredMigration();

        Deprecation::enableTrackingDeprecations();
        $deprecationsBefore = Deprecation::getTriggeredDeprecations()[self::PLATFORM_NAME_DEPRECATION] ?? 0;

        $migrate($migration);

        self::assertSame(
            $deprecationsBefore,
            Deprecation::getTriggeredDeprecations()[self::PLATFORM_NAME_DEPRECATION] ?? 0,
            'The migration must not call AbstractPlatform::getName(), which DBAL 4 removed.',
        );
        self::assertStringContainsString($expectedSql, $migration->getSql()[0]->getStatement());
    }

    /** @dataProvider directionProvider */
    public function testAbortsOnNonMysqlPlatform(callable $migrate): void
    {
        // The test application only provides a MySQL connection: build the migration on an in-memory SQLite one.
        $migration = new Version20200107151826(
            DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]),
            new NullLogger(),
        );

        $this->expectException(AbortMigration::class);
        $this->expectExceptionMessage('Migration can only be executed safely on \'mysql\'.');

        $migrate($migration);
    }

    public static function directionProvider(): iterable
    {
        yield 'up' => [static fn (AbstractMigration $migration) => $migration->up(new Schema()), 'CREATE TABLE scheduled_command'];
        yield 'down' => [static fn (AbstractMigration $migration) => $migration->down(new Schema()), 'DROP TABLE scheduled_command'];
    }

    private function getRegisteredMigration(): AbstractMigration
    {
        /** @var DependencyFactory $dependencyFactory */
        $dependencyFactory = self::getContainer()->get('doctrine.migrations.dependency_factory');

        return $dependencyFactory
            ->getMigrationRepository()
            ->getMigration(new Version(Version20200107151826::class))
            ->getMigration()
        ;
    }
}
