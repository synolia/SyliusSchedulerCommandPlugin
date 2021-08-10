<?php

declare(strict_types=1);

namespace Synolia\SyliusSchedulerCommandPlugin\Repository;

use Sylius\Component\Resource\Repository\RepositoryInterface;
use Synolia\SyliusSchedulerCommandPlugin\Entity\Command;

/**
 * @method Command|null find($id, $lockMode = null, $lockVersion = null)
 * @method Command|null findOneBy(array $criteria, array $orderBy = null)
 * @method Command[]    findAll()
 * @method Command[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
interface CommandRepositoryInterface extends RepositoryInterface
{
    public function findEnabledCommand(): iterable;
}
