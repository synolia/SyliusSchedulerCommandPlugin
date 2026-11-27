<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\PHPUnit;

trait CollectDeprecationsTrait
{
    /**
     * Runs the callable and returns the messages of the deprecations it triggered:
     * on Symfony 7.4, they flag the APIs that Symfony 8 removed.
     *
     * @return list<string>
     */
    private function collectDeprecations(callable $callable): array
    {
        $deprecations = [];
        set_error_handler(static function (int $type, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;

            return true;
        }, \E_USER_DEPRECATED);

        try {
            $callable();
        } finally {
            restore_error_handler();
        }

        return $deprecations;
    }
}
