<?php
declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\MbStrFunctionsFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(dirname(__DIR__) . '/vendor/sylius-labs/coding-standard/ecs.php');

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        dirname(__DIR__, 1) . '/src',
        dirname(__DIR__, 1) . '/tests/Behat',
        dirname(__DIR__, 1) . '/tests/PHPUnit',
        dirname(__DIR__, 1) . '/spec',
    ]);

    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::SYMFONY);
    $containerConfigurator->import(SetList::SYMFONY_RISKY);

    $services = $containerConfigurator->services();
    // PHP arrays should be declared using the configured syntax.
    $services->set(ArraySyntaxFixer::class)
             ->call('configure', [['syntax'=>'short']]);
    // Concatenation should be spaced according configuration.
    $services->set(ConcatSpaceFixer::class)
             ->call('configure', [['spacing'=>'one']]);
    // All classes must be final, except abstract ones and Doctrine entities.
    $services->set(FinalClassFixer::class);
    // Add, replace or remove header comment.
    $services->set(HeaderCommentFixer::class)
             ->call('configure', [['header'=>'']]);
    // Replace non multibyte-safe functions with corresponding mb function.
    $services->set(MbStrFunctionsFixer::class);
};
