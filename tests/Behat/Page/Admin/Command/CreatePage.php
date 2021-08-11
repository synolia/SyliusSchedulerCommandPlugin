<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\Command;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function fillField(string $field, string $value): void
    {
        $this->getDocument()->fillField($field, $value);
    }
}
