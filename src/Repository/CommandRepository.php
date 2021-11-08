<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Synolia\SyliusSchedulerCommandPlugin\Entity\CommandInterface;

final class CommandRepository extends EntityRepository implements CommandRepositoryInterface
{
    /**
     * @return CommandInterface[]
     */
    public function findEnabledCommand(): iterable
    {
        return $this->findBy(['enabled' => true], ['priority' => 'DESC']);
    }
}
