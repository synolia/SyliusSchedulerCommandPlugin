<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        dirname(__DIR__, 1) . '/src',
        dirname(__DIR__, 1) . '/tests/PHPUnit',
        dirname(__DIR__, 1) . '/spec',
    ]);

    $ecsConfig->import(dirname(__DIR__) . '/vendor/sylius-labs/coding-standard/ecs.php');

    /** @phpstan-ignore-next-line  */
    $ecsConfig->rule(\SlevomatCodingStandard\Sniffs\Classes\RequireMultiLineMethodSignatureSniff::class);
};
