<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Enum;

final class ScheduledCommandStateEnum
{
    public const WAITING = 'waiting';

    public const IN_PROGRESS = 'in_progress';

    public const FINISHED = 'finished';

    public const ERROR = 'error';
}
