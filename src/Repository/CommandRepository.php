<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

class CommandRepository extends EntityRepository implements CommandRepositoryInterface
{
    /**
     * @return CommandInterface[]
     */
    public function findEnabledCommand(): iterable
    {
        /** @var iterable<CommandInterface> $commands */
        $commands = $this->findBy(['enabled' => true], ['priority' => 'DESC']);

        return $commands;
    }
}
