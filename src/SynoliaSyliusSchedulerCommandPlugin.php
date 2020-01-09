<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SynoliaSyliusSchedulerCommandPlugin extends Bundle
{
    use SyliusPluginTrait;
}
