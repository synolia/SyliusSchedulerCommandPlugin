<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Validator;

use const DIRECTORY_SEPARATOR;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class LogfilePrefixPropertyValidator
{
    public static function validate(?string $object, ExecutionContextInterface $context): void
    {
        if (null === $object) {
            return;
        }

        if (strpos($object, DIRECTORY_SEPARATOR) !== false) {
            $context
                ->buildViolation('synolia.forms.constraints.cannot_use_slash', [
                    '%%DIRECTORY_SEPARATOR%%' => DIRECTORY_SEPARATOR,
                ])
                ->atPath('logFilePrefix')
                ->addViolation()
            ;
        }
    }
}
