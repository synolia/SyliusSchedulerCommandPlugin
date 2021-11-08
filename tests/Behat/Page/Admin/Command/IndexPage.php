<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusSchedulerCommandPlugin\Behat\Page\Admin\Command;

use Sylius\Behat\Page\Admin\Crud\IndexPage as BaseIndexPage;

final class IndexPage extends BaseIndexPage implements IndexPageInterface
{
    public function bulkEmptyLogs(): void
    {
        $this->getElement('bulk_actions')->pressButton('Empty logs');
        $this->getElement('confirmation_button')->click();
    }
}
