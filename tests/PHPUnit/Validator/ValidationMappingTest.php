<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\Validator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\YamlFileLoader;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;
use Synolia\SyliusSchedulerCommandPlugin\Entity\ScheduledCommand;
use Synolia\SyliusSchedulerCommandPlugin\Validator\LogfilePrefixPropertyValidator;
use Synolia\SyliusSchedulerCommandPlugin\Validator\LogfilePropertyValidator;
use Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit\CollectDeprecationsTrait;

final class ValidationMappingTest extends TestCase
{
    use CollectDeprecationsTrait;

    /** @dataProvider callbackConstraintProvider */
    public function testCallbackConstraintIsConfiguredWithNamedOptions(
        string $class,
        string $property,
        array $expectedCallback,
    ): void {
        $loader = new YamlFileLoader(\dirname(__DIR__, 3) . '/config/validation.yaml');
        $metadata = new ClassMetadata($class);

        // Symfony 8 passes a YAML list positionally to the constraint constructor: the method name would land in $groups.
        self::assertSame([], $this->collectDeprecations(static fn (): bool => $loader->loadClassMetadata($metadata)));

        $callbacks = array_values(array_filter(
            $metadata->getPropertyMetadata($property)[0]->getConstraints(),
            static fn (object $constraint): bool => $constraint instanceof Callback,
        ));
        self::assertCount(1, $callbacks);
        self::assertSame($expectedCallback, $callbacks[0]->callback);
        self::assertSame(['Default', (new \ReflectionClass($class))->getShortName()], $callbacks[0]->groups);
    }

    public static function callbackConstraintProvider(): iterable
    {
        yield 'Command::logFilePrefix' => [Command::class, 'logFilePrefix', [LogfilePrefixPropertyValidator::class, 'validate']];
        yield 'ScheduledCommand::logFile' => [ScheduledCommand::class, 'logFile', [LogfilePropertyValidator::class, 'validate']];
    }
}
