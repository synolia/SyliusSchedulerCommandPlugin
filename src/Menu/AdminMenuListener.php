<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Menu;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        /** @var ItemInterface $newSubmenu */
        $newSubmenu = $menu->addChild('scheduler');

        $newSubmenu->addChild('scheduler-command', [
                'route' => 'synolia_admin_command_index',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('sylius.menu.admin.main.configuration.scheduler_command')
            ->setLabelAttribute('icon', 'clock')
        ;
        $newSubmenu->addChild('scheduler-command-history', [
                'route' => 'synolia_admin_scheduled_command_index',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('sylius.menu.admin.main.configuration.scheduler_command_history')
            ->setLabelAttribute('icon', 'clock')
        ;
    }
}
