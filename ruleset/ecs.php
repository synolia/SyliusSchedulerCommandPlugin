<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use SlevomatCodingStandard\Sniffs\Classes\RequireMultiLineMethodSignatureSniff;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        dirname(__DIR__, 1) . '/src',
        dirname(__DIR__, 1) . '/tests/PHPUnit',
    ]);

    $ecsConfig->import(dirname(__DIR__) . '/vendor/sylius-labs/coding-standard/ecs.php');

    $ecsConfig->rules([
        OrderedClassElementsFixer::class,
        BinaryOperatorSpacesFixer::class,
    ]);

    /** @phpstan-ignore-next-line  */
    $ecsConfig->rule(RequireMultiLineMethodSignatureSniff::class);
};
