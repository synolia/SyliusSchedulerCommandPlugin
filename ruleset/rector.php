<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        dirname(__DIR__) . '/src',
        dirname(__DIR__) . '/spec',
        dirname(__DIR__) . '/tests/Behat',
        dirname(__DIR__) . '/tests/PHPUnit',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
        LevelSetList::UP_TO_PHP_80
    ]);
};
