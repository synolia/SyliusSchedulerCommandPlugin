<?php

declare(strict_types=1);

namespace Tests\Synolia\SchedulerCommandPlugin\Behat\Page\Admin\SchedulerCommand;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    public function fillField(string $field, string $value): void
    {
        $this->getDocument()->fillField($field, $value);
    }
}
