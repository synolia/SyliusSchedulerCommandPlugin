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
        $newSubmenu = $menu
            ->getChild('configuration');

        $newSubmenu->addChild('scheduler-command', [
                'route' => 'sylius_admin_scheduled_command_index',
            ])
            ->setAttribute('type', 'link')
            ->setLabel('sylius.menu.admin.main.configuration.scheduler_command')
            ->setLabelAttribute('icon', 'clock');
    }
}
