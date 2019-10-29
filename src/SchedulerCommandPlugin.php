<?php

declare(strict_types=1);

namespace Synolia\SchedulerCommandPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SchedulerCommandPlugin extends Bundle
{
    use SyliusPluginTrait;
}
