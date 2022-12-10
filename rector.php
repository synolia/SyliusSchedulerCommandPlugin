<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/spec',
        __DIR__ . '/src',
        __DIR__ . '/tests/PHPUnit',
        __DIR__ . '/tests/Behat',
    ]);

   $rectorConfig->sets([
       LevelSetList::UP_TO_PHP_74,
       LevelSetList::UP_TO_PHP_80
   ]);
};
