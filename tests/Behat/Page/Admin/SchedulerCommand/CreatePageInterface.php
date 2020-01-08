<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function fillField(string $field, string $value): void;
}
